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

    $middleLog = (new ActionLog)->setRawAttributes([
        'id' => 2,
        'type' => ActionLogType::System->value,
        'title' => 'customer updated',
        'body' => 'The customer record was updated.',
        'happened_at' => '2026-03-26 12:00:00',
    ])->setRelation('actor', null);

    $recentLog = (new ActionLog)->setRawAttributes([
        'id' => 3,
        'type' => ActionLogType::Note->value,
        'title' => 'follow up scheduled',
        'body' => null,
        'happened_at' => '2026-03-27 09:00:00',
    ])->setRelation('actor', null);

    $timelineActions = ActionLogTimelineMapper::map(new EloquentCollection([
        $olderLog,
        $middleLog,
        $recentLog,
    ]));

    $firstTimelineAction = $timelineActions->first();
    $middleTimelineAction = $timelineActions->get(1);
    $lastTimelineAction = $timelineActions->last();

    if (! $firstTimelineAction instanceof ActionLogDTO || ! $middleTimelineAction instanceof ActionLogDTO || ! $lastTimelineAction instanceof ActionLogDTO) {
        throw new RuntimeException('Expected timeline actions to contain first, middle, and last entries.');
    }

    expect($timelineActions)->toHaveCount(3)
        ->and($firstTimelineAction)->toBeInstanceOf(ActionLogDTO::class)
        ->and($firstTimelineAction->id)->toBe(3)
        ->and($firstTimelineAction->typeLabel)->toBe('Note')
        ->and($firstTimelineAction->typeColor)->toBe('gray')
        ->and($firstTimelineAction->title)->toBe('Follow up scheduled')
        ->and($firstTimelineAction->body)->toBe('No body was given')
        ->and($firstTimelineAction->happenedAt)->toBe(Carbon::parse('2026-03-27 09:00:00')->toString())
        ->and($firstTimelineAction->actorName)->toBe('System')
        ->and($firstTimelineAction->canManage)->toBeTrue()
        ->and($middleTimelineAction)->toBeInstanceOf(ActionLogDTO::class)
        ->and($middleTimelineAction->id)->toBe(2)
        ->and($middleTimelineAction->typeLabel)->toBe('System')
        ->and($middleTimelineAction->typeColor)->toBe('success')
        ->and($middleTimelineAction->title)->toBe('Customer updated')
        ->and($middleTimelineAction->body)->toBe('The customer record was updated.')
        ->and($middleTimelineAction->canManage)->toBeFalse()
        ->and($lastTimelineAction)->toBeInstanceOf(ActionLogDTO::class)
        ->and($lastTimelineAction->id)->toBe(1)
        ->and($lastTimelineAction->typeLabel)->toBe('Email')
        ->and($lastTimelineAction->typeColor)->toBe('primary')
        ->and($lastTimelineAction->title)->toBe('Untitled')
        ->and($lastTimelineAction->body)->toBe('Sent pricing summary')
        ->and($lastTimelineAction->happenedAt)->toBe(Carbon::parse('2026-03-26 08:00:00')->toString())
        ->and($lastTimelineAction->actorName)->toBe('Morgan Lee')
        ->and($lastTimelineAction->canManage)->toBeTrue()
    ;
});
