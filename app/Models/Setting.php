<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function backups()
    {
        return $this->hasMany(BackupSetting::class);
    }

    public function themes()
    {
        return $this->hasMany(Theme::class);
    }

    public function notifications()
    {
        return $this->hasMany(NotificationType::class);
    }

    public function currencies()
    {
        return $this->hasMany(Currency::class);
    }
}
