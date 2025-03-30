<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accounts()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function types()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function transferTransactions()
    {
        return $this->belongsTo(TransferTransaction::class);
    }

    public function transactionTags()
    {
        return $this->hasMany(TransactionTags::class);
    }

    public function transactionCategories()
    {
        return $this->hasMany(TransactionCategory::class);
    }
}
