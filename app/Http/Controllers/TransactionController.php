<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Installment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('client')->latest()->paginate(15);
        return view('transactions.index', compact('transactions'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        
        // Obtener los IDs de la URL si existen
        $selectedClientId = $request->query('client_id');
        $selectedInstallmentId = $request->query('installment_id');

        return view('transactions.create', compact('clients', 'selectedClientId', 'selectedInstallmentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'installments' => 'required|array',
            'installments.*' => 'exists:installments,id',
        ]);

        $amountPaid = floatval($validated['amount_paid']);
        $selectedInstallments = Installment::with('transactions')->find($validated['installments']);
        
        $totalDueForSelected = $selectedInstallments->reduce(function ($carry, $installment) {
            $totalOwed = $installment->base_amount + $installment->interest_amount;
            $totalPaid = $installment->transactions->sum('pivot.amount_applied');
            return $carry + ($totalOwed - $totalPaid);
        }, 0);

        if ($amountPaid > round($totalDueForSelected, 2) + 0.001) {
            throw ValidationException::withMessages([
                'amount_paid' => 'El monto a pagar no puede ser mayor que el adeudo de las cuotas seleccionadas (Total adeudado: $' . number_format($totalDueForSelected, 2) . ').'
            ]);
        }
        
        $client = Client::findOrFail($validated['client_id']);
        $amountToApply = $amountPaid;
        $transaction = null;

        try {
            DB::beginTransaction();

            $transaction = $client->transactions()->create([
                'amount_paid' => $amountToApply,
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'],
            ]);

            $installmentsToProcess = $selectedInstallments->sortBy('due_date');

            foreach ($installmentsToProcess as $installment) {
                if ($amountToApply <= 0) break;

                // Recalcular adeudo específico para esta cuota, sin contar la transacción actual
                $paidBeforeThisTransaction = $installment->transactions()->where('transaction_id', '!=', $transaction->id)->sum('amount_applied');
                $totalOwed = ($installment->base_amount + $installment->interest_amount) - $paidBeforeThisTransaction;
                $amountForThisInstallment = min($amountToApply, $totalOwed);

                if ($amountForThisInstallment > 0.001) {
                    $transaction->installments()->attach($installment->id, ['amount_applied' => $amountForThisInstallment]);
                }

                // Recalcular el total pagado incluyendo la transacción actual para actualizar el estado
                $totalPaidIncludingCurrent = $paidBeforeThisTransaction + $amountForThisInstallment;
                if ($totalPaidIncludingCurrent >= ($installment->base_amount + $installment->interest_amount) - 0.001) {
                    $installment->status = 'pagada';
                    $installment->save();
                }

                $amountToApply -= $amountForThisInstallment;
            }
            
            $transaction->folio_number = 'FOLIO-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
            $transaction->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Pago registrado exitosamente.')
            ->with('new_transaction_id', $transaction->id);
    }

    public function showPdf(Transaction $transaction)
    {
        $transaction->load('client', 'installments.paymentPlan.lot', 'installments.paymentPlan.service');
        
        $pdf = PDF::loadView('transactions.pdf', compact('transaction'));
        
        return $pdf->stream('recibo-'.$transaction->folio_number.'.pdf');
    }
}