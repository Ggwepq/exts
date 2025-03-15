<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function accountCategories()
    {
        return $this->hasMany(AccountCategory::class);
    }

    public function transactionCategory()
    {
        return $this->hasMany(TransactionCategory::class);
    }
}
