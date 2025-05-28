<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function accountCategories()
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
