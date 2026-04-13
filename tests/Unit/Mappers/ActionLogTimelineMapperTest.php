<?php

declare(strict_types=1);

use App\DTOs\ActionLog\ActionLogDTO;
use App\Enums\ActionLogType;
use App\Mappers\ActionLogTimelineMapper;
use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class);

it('builds timeline actions from action logs', function (): void {
    $actor = new User;
    $actor->name = 'Morgan Lee';

    $olderLog = (new ActionLog)->setRawAttributes([
        'id' => 1,
        'type' => ActionLogType::Email->value,
        'title' => null,
        'body' => 'Sent pricing summary',
        'happened_at' => '2026-03-26 08:00:00',
    ])->setRelation('actor', $actor);

    $recentLog = (new ActionLog)->setRawAttributes([
        'id' => 2,
        'type' => ActionLogType::System->value,
        'title' => 'follow up scheduled',
        'body' => null,
        'happened_at' => '2026-03-27 09:00:00',
    ])->setRelation('actor', null);

    $timelineActions = ActionLogTimelineMapper::map(new EloquentCollection([
        $olderLog,
        $recentLog,
    ]));

    $firstTimelineAction = $timelineActions->first();
    $lastTimelineAction = $timelineActions->last();

    if (!$firstTimelineAction instanceof ActionLogDTO || !$lastTimelineAction instanceof ActionLogDTO) {
        throw new RuntimeException('Expected timeline actions to contain first and last entries.');
    }

    expect($timelineActions)->toHaveCount(2)
        ->and($firstTimelineAction)->toBeInstanceOf(ActionLogDTO::class)
        ->and($firstTimelineAction->id)->toBe(2)
        ->and($firstTimelineAction->title)->toBe('Follow up scheduled')
        ->and($firstTimelineAction->body)->toBe('No body was given')
        ->and($firstTimelineAction->happenedAt)->toBe(Carbon::parse('2026-03-27 09:00:00')->toString())
        ->and($firstTimelineAction->actorName)->toBe('System')
        ->and($firstTimelineAction->canManage)->toBeFalse()
        ->and($lastTimelineAction)->toBeInstanceOf(ActionLogDTO::class)
        ->and($lastTimelineAction->id)->toBe(1)
        ->and($lastTimelineAction->title)->toBe('Untitled')
        ->and($lastTimelineAction->body)->toBe('Sent pricing summary')
        ->and($lastTimelineAction->happenedAt)->toBe(Carbon::parse('2026-03-26 08:00:00')->toString())
        ->and($lastTimelineAction->actorName)->toBe('Morgan Lee')
        ->and($lastTimelineAction->canManage)->toBeTrue()
    ;
});
