<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Installment;

class ClientInstallmentController extends Controller
{
    public function index(Client $client)
    {
        $installments = Installment::whereHas('paymentPlan.lot', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->with('paymentPlan.service', 'transactions')
            ->orderBy('due_date', 'asc')
            ->get();

        $installments->each(function ($installment) {
            $totalPaid = $installment->transactions->sum('pivot.amount_applied');
            $totalOwed = $installment->base_amount + $installment->interest_amount;
            $installment->remaining_balance = $totalOwed - $totalPaid;
            // CAMBIO: Añadir fecha formateada
            $installment->formatted_due_date = \Illuminate\Support\Carbon::parse($installment->due_date)->format('d/m/Y');
        });

        $pendingInstallments = $installments->filter(function ($installment) {
            return $installment->remaining_balance > 0.005;
        });

        return response()->json($pendingInstallments->values());
    }
}