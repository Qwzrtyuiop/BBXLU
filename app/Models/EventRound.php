<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'stage',
        'round_number',
        'label',
        'status',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(EventMatch::class);
    }
}
