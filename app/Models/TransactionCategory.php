<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionCategory extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function groups()
    {
        return $this->belongsTo(CategoryGroup::class, 'group_id');
    }

    public function types()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function budgets()
    {
        return $this->belongsTo(Budget::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
