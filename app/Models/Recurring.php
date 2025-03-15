<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recurring extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
