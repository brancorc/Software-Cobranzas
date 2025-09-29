<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotOwnershipHistory extends Model
{
    use HasFactory;
    protected $fillable = ['lot_id', 'previous_client_id', 'new_client_id', 'transfer_date', 'notes'];

    public function previousClient()
    {
        return $this->belongsTo(Client::class, 'previous_client_id');
    }

    public function newClient()
    {
        return $this->belongsTo(Client::class, 'new_client_id');
    }
}