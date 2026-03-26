<?php

declare(strict_types=1);

use App\DTOs\ActionLog\ActionLogDTO;
use App\Enums\ActionLogType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\ActionLog;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class);

it('casts the lead source and status to enums', function (): void {
    $lead = (new Lead)->setRawAttributes([
        'source' => LeadSource::ColdOutreach->value,
        'status' => LeadStatus::Qualified->value,
    ]);

    expect($lead->source)->toBe(LeadSource::ColdOutreach)
        ->and($lead->status)->toBe(LeadStatus::Qualified)
    ;
});

it('provides labels and colors for lead enums', function (): void {
    expect(LeadSource::ColdOutreach->getLabel())->toBe('Cold outreach')
        ->and(LeadSource::ColdOutreach->getColor())->toBe('warning')
        ->and(LeadSource::GooglePlus->getLabel())->toBe('Google+')
        ->and(LeadSource::GooglePlus->getColor())->toBe('danger')
        ->and(LeadStatus::Qualified->getLabel())->toBe('Qualified')
        ->and(LeadStatus::Qualified->getColor())->toBe('success')
        ->and(LeadStatus::Unqualified->getLabel())->toBe('Unqualified')
        ->and(LeadStatus::Unqualified->getColor())->toBe('danger')
    ;
});

it('builds timeline actions from related action logs', function (): void {
    $lead = new Lead;

    $actor = new User;
    $actor->name = 'Morgan Lee';

    $olderLog = (new ActionLog)->setRawAttributes([
        'type' => ActionLogType::Email->value,
        'title' => null,
        'body' => 'Sent pricing summary',
        'happened_at' => '2026-03-26 08:00:00',
    ]);
    $olderLog->setRelation('actor', $actor);

    $recentLog = (new ActionLog)->setRawAttributes([
        'type' => ActionLogType::Note->value,
        'title' => 'follow up scheduled',
        'body' => null,
        'happened_at' => '2026-03-27 09:00:00',
    ]);
    $recentLog->setRelation('actor', null);

    $lead->setRelation('actionLogs', new EloquentCollection([
        $olderLog,
        $recentLog,
    ]));

    $timelineActions = $lead->getTimelineActions();

    expect($timelineActions)->toHaveCount(2)
        ->and($timelineActions->first())->toBeInstanceOf(ActionLogDTO::class)
        ->and($timelineActions->first()->title)->toBe('Follow up scheduled')
        ->and($timelineActions->first()->body)->toBe('No body was given')
        ->and($timelineActions->first()->happenedAt)->toBe(Carbon::parse('2026-03-27 09:00:00')->toString())
        ->and($timelineActions->first()->actorName)->toBe('System')
        ->and($timelineActions->last())->toBeInstanceOf(ActionLogDTO::class)
        ->and($timelineActions->last()->title)->toBe('Untitled')
        ->and($timelineActions->last()->body)->toBe('Sent pricing summary')
        ->and($timelineActions->last()->happenedAt)->toBe(Carbon::parse('2026-03-26 08:00:00')->toString())
        ->and($timelineActions->last()->actorName)->toBe('Morgan Lee')
    ;
});
