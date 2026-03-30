<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventParticipant extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'player_id',
        'deck_name',
        'deck_bey1',
        'deck_bey2',
        'deck_bey3',
        'deck_registered_at',
    ];

    protected function casts(): array
    {
        return [
            'deck_registered_at' => 'datetime',
        ];
    }

    public function hasRegisteredDeck(): bool
    {
        return filled($this->deck_name)
            && filled($this->deck_bey1)
            && filled($this->deck_bey2)
            && filled($this->deck_bey3);
    }

    public function registeredBeys(): array
    {
        return array_values(array_filter([
            $this->deck_bey1,
            $this->deck_bey2,
            $this->deck_bey3,
        ], fn (?string $value) => filled($value)));
    }

    public function deckSummary(): string
    {
        if (! $this->hasRegisteredDeck()) {
            return 'Deck not registered';
        }

        return trim($this->deck_name.' - '.implode(', ', $this->registeredBeys()));
    }

    public function requiresDeckFor(Event $event, bool $enteringSingleElim = false): bool
    {
        return $event->usesLockedDecks() || $enteringSingleElim;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
