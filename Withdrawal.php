<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    const METHOD_BITCOIN = 'bitcoin'; // for bitcoin withdrawal

    const STATUS_PENDING = 'pending'; // status when we are waiting for acceptance by admin
    const STATUS_REJECTED = 'rejected'; // sstatus when the withdrawal is rejected by admin
    const STATUS_PROCEEDED = 'proceeded'; // status when the withdrawal is proceeded

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'method', 'status',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function methodWithdrawal()
    {
        switch ($this->method) {
            case 'bitcoin':
                return $this->hasOne(BitcoinWithdrawal::class);
        }
    }

    public function isPending()
    {
        return static::STATUS_PENDING === $this->status;
    }

    public function isRejected()
    {
        return static::STATUS_REJECTED === $this->status;
    }

    public function isProceeded()
    {
        return static::STATUS_PROCEEDED === $this->status;
    }

    public function scopeOfStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        } else {
            return $query->where('status', $status);
        }
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_null($value) and isset($this->methodWithdrawal)) {
            return $this->methodWithdrawal[$key];
        } else {
            return $value;
        }
    }
}
