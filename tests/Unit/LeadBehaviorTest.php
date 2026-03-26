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
        ->and(LeadSource::Website->getLabel())->toBe('Website')
        ->and(LeadSource::Website->getColor())->toBe('primary')
        ->and(LeadSource::Referral->getLabel())->toBe('Referral')
        ->and(LeadSource::Referral->getColor())->toBe('success')
        ->and(LeadSource::LinkedIn->getLabel())->toBe('LinkedIn')
        ->and(LeadSource::LinkedIn->getColor())->toBe('gray')
        ->and(LeadSource::Facebook->getLabel())->toBe('Facebook')
        ->and(LeadSource::Facebook->getColor())->toBe('gray')
        ->and(LeadSource::GooglePlus->getLabel())->toBe('Google+')
        ->and(LeadSource::GooglePlus->getColor())->toBe('danger')
        ->and(LeadSource::Xing->getLabel())->toBe('Xing')
        ->and(LeadSource::Xing->getColor())->toBe('gray')
        ->and(LeadSource::YouTube->getLabel())->toBe('YouTube')
        ->and(LeadSource::YouTube->getColor())->toBe('info')
        ->and(LeadSource::Instagram->getLabel())->toBe('Instagram')
        ->and(LeadSource::Instagram->getColor())->toBe('gray')
        ->and(LeadSource::XCom->getLabel())->toBe('X.com')
        ->and(LeadSource::XCom->getColor())->toBe('dark')
        ->and(LeadSource::Other->getLabel())->toBe('Other')
        ->and(LeadSource::Other->getColor())->toBe('gray')
        ->and(LeadStatus::New->getLabel())->toBe('New')
        ->and(LeadStatus::New->getColor())->toBe('gray')
        ->and(LeadStatus::Contacted->getLabel())->toBe('Contacted')
        ->and(LeadStatus::Contacted->getColor())->toBe('info')
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
    ])->setRelation('actor', $actor);

    $recentLog = (new ActionLog)->setRawAttributes([
        'type' => ActionLogType::Note->value,
        'title' => 'follow up scheduled',
        'body' => null,
        'happened_at' => '2026-03-27 09:00:00',
    ])->setRelation('actor', null);

    $lead->setRelation('actionLogs', new EloquentCollection([
        $olderLog,
        $recentLog,
    ]));

    $timelineActions = $lead->getTimelineActions();
    $firstTimelineAction = $timelineActions->first();
    $lastTimelineAction = $timelineActions->last();

    if (!$firstTimelineAction instanceof ActionLogDTO || !$lastTimelineAction instanceof ActionLogDTO) {
        throw new RuntimeException('Expected timeline actions to contain first and last entries.');
    }

    expect($timelineActions)->toHaveCount(2)
        ->and($firstTimelineAction)->toBeInstanceOf(ActionLogDTO::class)
        ->and($firstTimelineAction->title)->toBe('Follow up scheduled')
        ->and($firstTimelineAction->body)->toBe('No body was given')
        ->and($firstTimelineAction->happenedAt)->toBe(Carbon::parse('2026-03-27 09:00:00')->toString())
        ->and($firstTimelineAction->actorName)->toBe('System')
        ->and($lastTimelineAction)->toBeInstanceOf(ActionLogDTO::class)
        ->and($lastTimelineAction->title)->toBe('Untitled')
        ->and($lastTimelineAction->body)->toBe('Sent pricing summary')
        ->and($lastTimelineAction->happenedAt)->toBe(Carbon::parse('2026-03-26 08:00:00')->toString())
        ->and($lastTimelineAction->actorName)->toBe('Morgan Lee')
    ;
});
