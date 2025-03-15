<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    protected $guarded = [];

    public function settings()
    {
        return $this->belongsTo(Setting::class);
    }
}
