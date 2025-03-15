<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionTags extends Model
{
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function transactions()
    {
        return $this->hasOne(Transaction::class, 'transaction_id');
    }
}
