<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountCategory extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function categoryGroups()
    {
        return $this->belongsTo(CategoryGroup::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'category_id');
    }
}
