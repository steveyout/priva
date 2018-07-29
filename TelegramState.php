<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramState extends Model
{
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'telegram_id', 'status', 'command', 'arguments', 'data',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public static function last($id)
    {
        return TelegramState::where('telegram_id', $id)->latest()->first();
    }

    public static function reset($id)
    {
        $state = static::last($id);
        if ($state and 'none' !== $state->status) {
            static::create(['telegram_id' => $id, 'status' => 'none']);
        }
    }
}
