<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class BitcoinWithdrawal extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'withdrawal_id', 'address',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $casts = [
    ];

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class);
    }
}
