<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalProgress extends Model
{
    //
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }


    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

}
