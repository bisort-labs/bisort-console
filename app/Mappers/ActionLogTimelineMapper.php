<?php

declare(strict_types=1);

namespace App\Mappers;

use App\DTOs\ActionLog\ActionLogDTO;
use App\Models\ActionLog;
use App\Support\Localization;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ActionLogTimelineMapper
{
    /**
     * @param  EloquentCollection<int, ActionLog>  $actionLogs
     *
     * @return Collection<int, ActionLogDTO>
     */
    public static function map(EloquentCollection $actionLogs): Collection
    {
        return $actionLogs
            ->loadMissing('actor')
            ->sortByDesc(static fn (ActionLog $actionLog): int => $actionLog->happened_at->getTimestamp())
            ->values()
            ->map(
                static fn (ActionLog $actionLog): ActionLogDTO => new ActionLogDTO(
                    title: Str::ucfirst($actionLog->title ?? Localization::translate('messages.timeline.untitled')),
                    body: $actionLog->body ?? Localization::translate('messages.timeline.no_body_given'),
                    happenedAt: $actionLog->happened_at->toString(),
                    actorName: $actionLog->actor->name ?? Localization::translate('messages.timeline.system'),
                )
            )
        ;
    }
}
