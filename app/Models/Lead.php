<?php

declare(strict_types=1);

namespace App\Models;

use App\DTOs\ActionLog\ActionLogDTO;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Support\Localization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;

/**
 * @property LeadSource|null $source
 * @property LeadStatus|null $status
 * @property EloquentCollection<int, ActionLog> $actionLogs
 * @property User|null $owner
 */
#[Fillable([
    'name',
    'email',
    'company',
    'street',
    'city',
    'state',
    'zip',
    'country',
    'phone',
    'source',
    'status',
])]
class Lead extends Model
{
    use HasTimestamps, SoftDeletes;

    /**
     * @return Collection<int, ActionLogDTO>
     */
    public function getTimelineActions(): Collection
    {
        return $this->actionLogs
            ->loadMissing('actor')
            ->sortByDesc(static fn (ActionLog $actionLog): int => $actionLog->happened_at->getTimestamp())
            ->values()
            ->map(static fn (ActionLog $actionLog): ActionLogDTO => new ActionLogDTO(
                title: Str::ucfirst($actionLog->title ?? Localization::translate('messages.timeline.untitled')),
                body: $actionLog->body ?? Localization::translate('messages.timeline.no_body_given'),
                happenedAt: $actionLog->happened_at->toString(),
                actorName: $actionLog->actor->name ?? Localization::translate('messages.timeline.system'),
            ))
        ;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return MorphMany<ActionLog, $this>
     */
    public function actionLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'actionable');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'source' => LeadSource::class,
        ];
    }
}
