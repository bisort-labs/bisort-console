<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DealStage;
use App\Models\Concerns\HasActionLogs;
use Database\Factories\DealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property Carbon|null $close_date
 * @property DealStage $stage
 * @property EloquentCollection<int, ActionLog> $actionLogs
 * @property Lead $lead
 * @property User|null $owner
 * @property ClientProject|null $project
 */
#[Fillable([
    'lead_id',
    'project_id',
    'title',
    'stage',
    'expected_value_cents',
    'currency',
    'probability',
    'close_date',
    'lost_reason',
    'notes',
    'owner_id',
])]
class Deal extends Model
{
    /** @use HasFactory<DealFactory> */
    use HasActionLogs, HasFactory, HasTimestamps, SoftDeletes;

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsTo<ClientProject, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'project_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'stage' => DealStage::class,
            'close_date' => 'date',
            'expected_value_cents' => 'int',
            'probability' => 'int',
        ];
    }
}
