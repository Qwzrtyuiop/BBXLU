<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class EventMatch extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;
    public const FINISH_TYPE_POINTS = [
        'spin' => 1,
        'burst' => 2,
        'over' => 2,
        'extreme' => 3,
    ];

    protected $table = 'matches';

    protected $fillable = [
        'event_id',
        'event_round_id',
        'stage',
        'player1_id',
        'player2_id',
        'player1_score',
        'player2_score',
        'winner_id',
        'round_number',
        'match_number',
        'status',
        'is_bye',
        'source_match1_id',
        'source_match2_id',
        'result_1',
        'result_2',
        'result_3',
        'result_4',
        'result_5',
        'result_6',
        'result_7',
        'result_type_1',
        'result_type_2',
        'result_type_3',
        'result_type_4',
        'result_type_5',
        'result_type_6',
        'result_type_7',
        'player1_bey1',
        'player1_bey2',
        'player1_bey3',
        'player2_bey1',
        'player2_bey2',
        'player2_bey3',
    ];

    protected function casts(): array
    {
        return [
            'is_bye' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(EventRound::class, 'event_round_id');
    }

    public function player1(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function sourceMatch1(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_match1_id');
    }

    public function sourceMatch2(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_match2_id');
    }

    public function resultValues(): Collection
    {
        return collect(range(1, 7))
            ->map(fn (int $index) => $this->{"result_{$index}"})
            ->filter(fn ($value) => $value !== null)
            ->values();
    }

    public function battleResults(): Collection
    {
        return collect(range(1, 7))
            ->map(function (int $index) {
                $winner = $this->{"result_{$index}"};

                if ($winner === null) {
                    return null;
                }

                return [
                    'slot' => $index,
                    'winner' => $winner,
                    'type' => $this->{"result_type_{$index}"},
                ];
            })
            ->filter()
            ->values();
    }

    public static function finishTypePoints(?string $finishType): int
    {
        return self::FINISH_TYPE_POINTS[$finishType] ?? 1;
    }

    public function weightedBattlePointsForPlayer(int $playerId): int
    {
        if ($this->is_bye) {
            return $this->player1_id === $playerId ? (int) $this->player1_score : 0;
        }

        $winnerSlot = $this->player1_id === $playerId
            ? 1
            : ($this->player2_id === $playerId ? 2 : null);

        if ($winnerSlot === null) {
            return 0;
        }

        return $this->battleResults()->sum(function (array $battleResult) use ($winnerSlot) {
            if ((int) $battleResult['winner'] !== $winnerSlot) {
                return 0;
            }

            return self::finishTypePoints($battleResult['type']);
        });
    }
}
