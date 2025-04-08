<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionTags extends Model
{
    protected $fillable = ['transaction_id', 'tag_id'];
    
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
