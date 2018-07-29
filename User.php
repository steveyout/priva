<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'bitcoin_balance', 'referrer_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'referral_token', 'remember_token',
    ];

    protected $casts = [
        'bitcoin_balance' => 'float',
        'is_admin' => 'boolean',
    ];

    public static function boot()
    {
        static::creating(function ($user) {
            $user['referral_token'] = uniqid();
        });
    }

    public function telegramAccounts()
    {
        return $this->hasMany(TelegramAccount::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    public function referralCommissions()
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_id', 'id');
    }

    public function commissions()
    {
        return $this->hasMany(ReferralCommission::class, 'user_id', 'id');
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }
}
