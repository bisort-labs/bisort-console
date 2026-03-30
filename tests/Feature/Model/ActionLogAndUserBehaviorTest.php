<?php

declare(strict_types=1);

use App\Enums\ActionLogType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\ActionLog;
use App\Models\Lead;
use App\Models\User;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

it('casts action log attributes and resolves its relationships', function (): void {
    $actor = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Acme Prospect',
        'email' => 'acme@example.com',
        'source' => LeadSource::Website->value,
        'status' => LeadStatus::New->value,
    ]);

    $actionLog = $lead->actionLogs()->create([
        'type' => ActionLogType::Email->value,
        'title' => 'Sent recap',
        'body' => 'Shared a short recap after the call.',
        'actor_id' => $actor->getKey(),
        'happened_at' => '2026-03-27 10:30:00',
    ]);

    $actionLog->load(['actor', 'actionable']);

    expect($actionLog->type)->toBe(ActionLogType::Email)
        ->and($actionLog->happened_at)->toBeInstanceOf(Carbon::class)
        ->and($actionLog->actor)->toBeInstanceOf(User::class)
        ->and($actionLog->actor?->is($actor))->toBeTrue()
        ->and($actionLog->actionable)->toBeInstanceOf(Lead::class)
        ->and($actionLog->actionable?->is($lead))->toBeTrue()
    ;
});

it('defines user relations and casts', function (): void {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Northwind Prospect',
        'email' => 'northwind@example.com',
        'source' => LeadSource::Referral->value,
        'status' => LeadStatus::Qualified->value,
    ]);
    $lead->owner()->associate($user);
    $lead->save();

    $actionLog = $lead->actionLogs()->create([
        'type' => ActionLogType::Note->value,
        'title' => 'Follow up scheduled',
        'actor_id' => $user->getKey(),
        'happened_at' => '2026-03-27 11:00:00',
    ]);

    $user->password = 'secret-password';
    $user->load(['leads', 'actionLogs']);

    $castedUser = (new User)->setRawAttributes([
        'email_verified_at' => '2026-03-27 09:00:00',
    ]);

    expect($user->leads)->toHaveCount(1)
        ->and($user->leads->first()?->is($lead))->toBeTrue()
        ->and($user->actionLogs)->toHaveCount(1)
        ->and($user->actionLogs->first()?->is($actionLog))->toBeTrue()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue()
        ->and($castedUser->email_verified_at)->toBeInstanceOf(Carbon::class)
    ;
});

it('only allows users to access the console panel', function (): void {
    $user = new User;

    $consolePanel = (new Panel)->id('console');
    $marketingPanel = (new Panel)->id('marketing');

    expect($user->canAccessPanel($consolePanel))->toBeTrue()
        ->and($user->canAccessPanel($marketingPanel))->toBeFalse()
    ;
});

it('returns the expected relation objects for user and action log models', function (): void {
    $user = new User;
    $actionLog = new ActionLog;

    expect($user->leads())->toBeInstanceOf(HasMany::class)
        ->and($user->actionLogs())->toBeInstanceOf(HasMany::class)
        ->and($actionLog->actor())->toBeInstanceOf(BelongsTo::class)
        ->and($actionLog->actionable())->toBeInstanceOf(MorphTo::class)
    ;
});
