<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'challonge_link',
        'challonge_url',
        'event_type_id',
        'bracket_type',
        'swiss_rounds',
        'top_cut_size',
        'match_format',
        'date',
        'location',
        'status',
        'is_active',
        'swiss_king_player_id',
        'bird_king_player_id',
        'bracket_status',
        'is_lock_deck',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_active' => 'boolean',
            'is_lock_deck' => 'boolean',
            'swiss_rounds' => 'integer',
            'top_cut_size' => 'integer',
            'match_format' => 'integer',
        ];
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class)->withDefault([
            'name' => 'Unassigned',
        ]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault([
            'nickname' => 'Unknown creator',
            'name' => 'Unknown creator',
            'email' => null,
            'role' => 'user',
            'is_claimed' => false,
        ]);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'event_participants');
    }

    public function results(): HasMany
    {
        return $this->hasMany(EventResult::class);
    }

    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(EventAward::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(EventMatch::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(EventRound::class);
    }

    public function usesSwissBracket(): bool
    {
        return $this->bracket_type === 'swiss_single_elim';
    }

    public function usesLockedDecks(): bool
    {
        return (bool) $this->is_lock_deck;
    }

    public function bracketLabel(): string
    {
        return $this->usesSwissBracket()
            ? 'Swiss + Single Elimination'
            : 'Single Elimination';
    }

    public function battleWinThreshold(): int
    {
        return (int) floor(max(1, $this->match_format) / 2) + 1;
    }

    public function battleWinThresholdForStage(?string $stage = null, ?int $roundMatchCount = null): int
    {
        if (
            $this->usesSwissBracket()
            && $stage === 'single_elim'
            && $roundMatchCount === 1
        ) {
            return 7;
        }

        return $this->battleWinThreshold();
    }

    public function maxBattleSlotsForThreshold(int $threshold): int
    {
        return max(7, ($threshold * 2) - 1);
    }

    public function hasStarted(): bool
    {
        if (in_array($this->bracket_status, ['in_progress', 'completed'], true)) {
            return true;
        }

        if ($this->relationLoaded('rounds')) {
            return $this->rounds->isNotEmpty();
        }

        return $this->rounds()->exists();
    }

    public function storedChallongeLink(): ?string
    {
        $link = $this->challonge_link ?: $this->challonge_url;

        return is_string($link) && $link !== '' ? $link : null;
    }

    public function resolvedChallongeLink(): ?string
    {
        $storedLink = $this->storedChallongeLink();

        if ($storedLink && filter_var($storedLink, FILTER_VALIDATE_URL)) {
            return $storedLink;
        }

        $description = (string) ($this->description ?? '');

        if (
            preg_match('/https?:\/\/(?:www\.)?challonge\.com\/[^\s)]+/i', $description, $matches) === 1 ||
            preg_match('/https?:\/\/[^\s)]+/i', $description, $matches) === 1
        ) {
            return rtrim($matches[0], '.,;!?)]');
        }

        return filter_var($this->location, FILTER_VALIDATE_URL) ? $this->location : null;
    }
}
