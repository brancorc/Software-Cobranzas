<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function condoneInterest(Installment $installment)
    {
        $installment->update(['interest_amount' => 0]);

        return back()->with('success', 'Intereses condonados exitosamente.');
    }
}