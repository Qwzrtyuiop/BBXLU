<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventResult extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'player_id',
        'placement',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class)->withDefault([
            'title' => 'Unknown event',
        ]);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class)->withDefault();
    }
}
