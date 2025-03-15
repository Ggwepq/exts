<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    //
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function goalProgress()
    {
        return $this->hasMany(GoalProgress::class);
    }
}
