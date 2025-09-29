<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function incomeReport(Request $request)
    {
        // Establecer fechas por defecto al mes actual si no se proporcionan
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $transactions = Transaction::with('client')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'desc')
            ->get();
            
        $totalIncome = $transactions->sum('amount_paid');

        return view('reports.income', compact('transactions', 'totalIncome', 'startDate', 'endDate'));
    }
}