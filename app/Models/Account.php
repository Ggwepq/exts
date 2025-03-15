<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function accountCategories()
    {
        return $this->belongsTo(AccountCategory::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
