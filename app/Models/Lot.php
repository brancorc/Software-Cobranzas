<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'identifier', 'total_price', 'status'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentPlans()
    {
        return $this->hasMany(PaymentPlan::class);
    }

    public function ownershipHistory()
    {
        return $this->hasMany(LotOwnershipHistory::class)->latest();
    }

}