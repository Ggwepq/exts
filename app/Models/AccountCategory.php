<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCategory extends Model
{
    use HasFactory;

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
