<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    const METHOD_BITCOIN = 'bitcoin'; // for investing bitcoin

    const STATUS_WAITING = 'waiting'; // status when we are waiting for payment
    const STATUS_PENDING = 'pending'; // status when we are waiting for acceptance by admin
    const STATUS_REJECTED = 'rejected'; // status when the investment is rejected by admin
    const STATUS_STARTED = 'started'; // status when the investment is accepted by admin
    const STATUS_COMPLETED = 'completed'; // status when the investment is completed

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'amount', 'method', 'status',
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
        'started_at',
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }

    public function withdrawals()
    {
        return $this->morphMany(Withdrawal::class, 'withdrawable');
    }

    public function isWaiting()
    {
        return static::STATUS_WAITING === $this->status;
    }

    public function isPending()
    {
        return static::STATUS_PENDING === $this->status;
    }

    public function isRejected()
    {
        return static::STATUS_REJECTED === $this->status;
    }

    public function isStarted()
    {
        return static::STATUS_STARTED === $this->status;
    }

    public function isCompleted()
    {
        return static::STATUS_COMPLETED === $this->status;
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;

        switch ($value) {
            case static::STATUS_STARTED:
                $this->attributes['started_at'] = new DateTime();
                break;
            case static::STATUS_COMPLETED:
                $this->attributes['completed_at'] = new DateTime();
                break;
        }
    }

    public function hasProfit()
    {
        return $this->isStarted() || $this->isCompleted();
    }

    public function timeSpend()
    {
        return $this->started_at->diff($this->completed_at ?: new DateTime());
    }

    public function daysSpend()
    {
        return $this->timeSpend()->format('%a');
    }

    public function heartbit()
    {
        $this->profit += (4 * $this->amount) / 100;

        if (40 <= $this->daysSpend()) {
            $this->status = 'completed';
        }

        $this->save();
    }

    public function scopeOfStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        } else {
            return $query->where('status', $status);
        }
    }
}
