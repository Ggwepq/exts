<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_image_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurrings()
    {
        return $this->hasMany(Recurring::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function accountCategories()
    {
        return $this->hasMany(AccountCategory::class);
    }

    public function transactionCategories()
    {
        return $this->hasMany(TransactionCategory::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function goalProgress()
    {
        return $this->hasMany(GoalProgress::class);
    }

    public function categoryGroups()
    {
        return $this->hasMany(CategoryGroup::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}
