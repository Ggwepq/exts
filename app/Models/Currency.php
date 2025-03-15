<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $guarded = [];

    public function settings()
    {
        return $this->belongsTo(Setting::class, 'code');
    }
}
