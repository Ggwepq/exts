<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function transactionTags()
    {
        return $this->hasMany(TransactionTags::class);
    }

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'transaction_tags', 'tag_id', 'transaction_id');
    }
}
