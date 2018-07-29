<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const METHOD_BITCOIN = 'bitcoin'; // for bitcoin transaction

    const STATUS_WAITING = 'waiting'; // status when we are waiting for payment
    const STATUS_EXPIRED = 'expired'; // status when the transaction didn't recived any payment
    const STATUS_COMPLETED = 'completed'; // status when the transaction recived payment

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'transactable_id', 'transactable_type', 'amount', 'method', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    protected $dates = [
        'completed_at',
    ];

    public static function boot()
    {
        static::retrieved(function ($transaction) {
            if (!$transaction->isExpired()) {
                if (time() > $transaction->expireTime()->getTimestamp()) {
                    $transaction->update(['status' => static::STATUS_EXPIRED]);
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function methodTransaction()
    {
        switch ($this->method) {
            case 'bitcoin':
                return $this->hasOne(BitcoinTransaction::class);
        }
    }

    public function transactable()
    {
        return $this->morphTo();
    }

    public function isWaiting()
    {
        return static::STATUS_WAITING === $this->status;
    }

    public function isExpired()
    {
        return static::STATUS_EXPIRED === $this->status;
    }

    public function isCompleted()
    {
        return static::STATUS_COMPLETED === $this->status;
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;

        switch ($value) {
            case static::STATUS_COMPLETED:
                $this->attributes['completed_at'] = new DateTime();
                break;
        }
    }

    public function expireTime()
    {
        return clone $this->created_at->add(date_interval_create_from_date_string('30 minute'));
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_null($value) and isset($this->methodTransaction)) {
            return $this->methodTransaction[$key];
        } else {
            return $value;
        }
    }
}
