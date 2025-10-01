<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Installment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['client', 'installments'])
            ->latest('payment_date')
            ->paginate(15);
            
        return view('transactions.index', compact('transactions'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        
        $selectedClientId = $request->query('client_id');
        $selectedInstallmentId = $request->query('installment_id');

        return view('transactions.create', compact('clients', 'selectedClientId', 'selectedInstallmentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
            'installments' => 'required|array|min:1',
            'installments.*' => 'exists:installments,id',
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $amountPaid = (float) $validated['amount_paid'];

        // Validar que las cuotas pertenecen al cliente
        $selectedInstallments = Installment::whereIn('id', $validated['installments'])
            ->whereHas('paymentPlan.lot', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->with(['transactions', 'paymentPlan.lot'])
            ->lockForUpdate()
            ->get();

        if ($selectedInstallments->count() !== count($validated['installments'])) {
            throw ValidationException::withMessages([
                'installments' => 'Algunas cuotas seleccionadas no pertenecen a este cliente.'
            ]);
        }

        // Calcular el total adeudado de las cuotas seleccionadas
        $totalDueForSelected = $selectedInstallments->reduce(function ($carry, $installment) {
            $totalOwed = $installment->base_amount + $installment->interest_amount;
            $totalPaid = $installment->transactions->sum('pivot.amount_applied');
            return $carry + max(0, $totalOwed - $totalPaid);
        }, 0);

        // Validar que el monto no exceda el adeudo
        if ($amountPaid > round($totalDueForSelected, 2) + 0.01) {
            throw ValidationException::withMessages([
                'amount_paid' => sprintf(
                    'El monto a pagar ($%s) no puede ser mayor que el adeudo total de las cuotas seleccionadas ($%s).',
                    number_format($amountPaid, 2),
                    number_format($totalDueForSelected, 2)
                )
            ]);
        }

        $transaction = null;

        try {
            DB::beginTransaction();

            // Crear la transacción
            $transaction = $client->transactions()->create([
                'amount_paid' => $amountPaid,
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'],
            ]);

            // Aplicar el pago a las cuotas (ordenadas por fecha de vencimiento)
            $amountRemaining = $amountPaid;
            $installmentsSorted = $selectedInstallments->sortBy('due_date');

            foreach ($installmentsSorted as $installment) {
                if ($amountRemaining <= 0.001) {
                    break;
                }

                $totalOwed = $installment->base_amount + $installment->interest_amount;
                $alreadyPaid = $installment->transactions->sum('pivot.amount_applied');
                $amountDue = max(0, $totalOwed - $alreadyPaid);

                if ($amountDue > 0.001) {
                    $amountToApply = min($amountRemaining, $amountDue);
                    
                    $transaction->installments()->attach($installment->id, [
                        'amount_applied' => round($amountToApply, 2)
                    ]);

                    // Actualizar estado de la cuota si está completamente pagada
                    $newTotalPaid = $alreadyPaid + $amountToApply;
                    if (abs($newTotalPaid - $totalOwed) < 0.01) {
                        $installment->status = 'pagada';
                        $installment->save();
                    }

                    $amountRemaining -= $amountToApply;
                }
            }

            // Generar número de folio
            $transaction->folio_number = 'FOLIO-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
            $transaction->save();

            // Log de auditoría
            Log::info('Pago registrado exitosamente', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'amount' => $amountPaid,
                'installments_count' => $selectedInstallments->count(),
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Pago registrado exitosamente.')
                ->with('new_transaction_id', $transaction->id);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al procesar pago', [
                'client_id' => $client->id,
                'amount' => $amountPaid,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()
                ->with('error', 'Ocurrió un error al procesar el pago. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    public function showPdf(Transaction $transaction)
    {
        $transaction->load(['client', 'installments.paymentPlan.lot', 'installments.paymentPlan.service']);
        
        $pdf = PDF::loadView('transactions.pdf', compact('transaction'));
        
        return $pdf->stream('recibo-' . $transaction->folio_number . '.pdf');
    }
}