<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralCommission extends Model
{
    const METHOD_BITCOIN = 'bitcoin'; // for bitcoin commission

    const TYPE_INVESTMENT = 'investment'; // commission for referred investment

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'referrer_id', 'method', 'type', 'percent', 'amount',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $dates = [
    ];

    public static function addInvestment($user, $referrer, $realAmount)
    {
        $percent = 10;

        $amount = ($percent * $realAmount) / 100;

        $referrer->bitcoin_balance += $amount;
        $referrer->save();

        $commission = new ReferralCommission();
        $commission->user_id = $user->id;
        $commission->referrer_id = $referrer->id;
        $commission->type = 'investment';
        $commission->method = 'bitcoin';
        $commission->percent = $percent;
        $commission->amount = $amount;
        $commission->save();
    }
}
