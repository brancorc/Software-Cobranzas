<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'installment_number',
        'due_date',
        'base_amount',
        'interest_amount',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class)->withPivot('amount_applied');
    }

}