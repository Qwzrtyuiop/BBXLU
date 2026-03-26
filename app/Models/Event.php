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
        'date',
        'location',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'event_participants');
    }

    public function results(): HasMany
    {
        return $this->hasMany(EventResult::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(EventAward::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(EventMatch::class);
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
