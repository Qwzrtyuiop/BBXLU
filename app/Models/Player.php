<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'nickname' => 'Unknown player',
            'name' => 'Unknown player',
            'email' => null,
            'role' => 'user',
            'is_claimed' => false,
        ]);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_participants');
    }

    public function eventParticipations(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(EventResult::class);
    }
}
