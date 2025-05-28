<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function accountCategories()
    {
        return $this->hasMany(AccountCategory::class, 'group_id');
    }

    public function transactionCategories()
    {
        return $this->hasMany(TransactionCategory::class, 'group_id');
    }
}
