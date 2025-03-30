<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferTransaction extends Model
{
    protected $guarded = [];

    public function transfer()
    {
        return $this->hasMany(Transfer::class, 'transfer_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
