<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class BitcoinTransaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id', 'address',
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

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
