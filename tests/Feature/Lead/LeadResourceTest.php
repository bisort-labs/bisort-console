<?php

declare(strict_types=1);

use App\Enums\ActionLogType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Leads\Pages\CreateLead;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Models\ActionLog;
use App\Models\Lead;
use App\Models\User;
use App\Services\Localization;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\withSession;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
});

it('creates a lead via the Filament form', function (): void {
    $user = User::factory()->create();
    $owner = User::factory()->create();

    actingAs($user);

    livewire(CreateLead::class)
        ->fillForm([
            'name' => 'Northwind Prospect',
            'email' => 'northwind@example.com',
            'company' => 'Northwind GmbH',
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
            'state' => 'Berlin',
            'zip' => '10117',
            'country' => 'Germany',
            'phone' => '+49 30 123456',
            'source' => LeadSource::Referral->value,
            'status' => LeadStatus::Contacted->value,
            'owner_id' => $owner->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect()
    ;

    assertDatabaseHas(Lead::class, [
        'name' => 'Northwind Prospect',
        'email' => 'northwind@example.com',
        'company' => 'Northwind GmbH',
        'street' => 'Unter den Linden 1',
        'city' => 'Berlin',
        'state' => 'Berlin',
        'zip' => '10117',
        'country' => 'Germany',
        'phone' => '+49 30 123456',
        'source' => LeadSource::Referral->value,
        'status' => LeadStatus::Contacted->value,
        'owner_id' => $owner->getKey(),
    ]);
});

it('requires the email address to be unique', function (): void {
    $user = User::factory()->create();

    Lead::query()->create([
        'name' => 'Existing Prospect',
        'email' => 'existing@example.com',
        'source' => LeadSource::Website->value,
        'status' => LeadStatus::New->value,
    ]);

    actingAs($user);

    livewire(CreateLead::class)
        ->fillForm([
            'name' => 'Duplicate Prospect',
            'email' => 'existing@example.com',
            'source' => LeadSource::LinkedIn->value,
            'status' => LeadStatus::Qualified->value,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'email' => 'unique',
        ])
    ;
});

it('allows an authenticated user to access the list page', function (): void {
    $user = User::factory()->create();

    $leads = collect([
        Lead::query()->create([
            'name' => 'Acme Prospect',
            'email' => 'acme@example.com',
            'company' => 'Acme Inc.',
            'phone' => '+49 30 123456',
            'source' => LeadSource::ColdOutreach->value,
            'status' => LeadStatus::Qualified->value,
        ]),
        Lead::query()->create([
            'name' => 'Helios Prospect',
            'email' => 'helios@example.com',
            'company' => 'Helios AG',
            'phone' => '+49 89 987654',
            'source' => LeadSource::Website->value,
            'status' => LeadStatus::Contacted->value,
        ]),
    ]);

    actingAs($user);

    get(LeadResource::getUrl('index'))
        ->assertOk()
    ;

    livewire(ListLeads::class)
        ->assertCanSeeTableRecords($leads)
    ;
});

it('allows an authenticated user to access the edit page', function (): void {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Client Operations Prospect',
        'email' => 'client-operations@example.com',
        'company' => 'Northwind GmbH',
        'phone' => '+49 40 123456',
        'source' => LeadSource::Referral->value,
        'status' => LeadStatus::Contacted->value,
    ]);

    actingAs($user);

    get(LeadResource::getUrl('edit', ['record' => $lead]))
        ->assertOk()
        ->assertSeeText('Save changes')
        ->assertSeeText('Email address')
        ->assertSeeText('Owner')
    ;
});

it('logs lead detail updates as a single system timeline entry', function (): void {
    $user = User::factory()->create(['name' => 'Morgan Lee']);
    $owner = User::factory()->create(['name' => 'Jamie Fox']);

    $lead = Lead::query()->create([
        'name' => 'Northwind Prospect',
        'email' => 'northwind@example.com',
        'company' => 'Northwind GmbH',
        'street' => 'Unter den Linden 1',
        'city' => 'Berlin',
        'state' => 'Berlin',
        'zip' => '10117',
        'country' => 'Germany',
        'phone' => '+49 30 123456',
        'source' => LeadSource::ColdOutreach->value,
        'status' => LeadStatus::Contacted->value,
    ]);

    actingAs($user);

    livewire(EditLead::class, ['record' => $lead->getKey()])
        ->fillForm([
            'name' => 'Northwind Qualified Prospect',
            'email' => 'northwind@example.com',
            'company' => 'Northwind GmbH',
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
            'state' => 'Berlin',
            'zip' => '10117',
            'country' => 'Germany',
            'phone' => '+49 30 987654',
            'source' => LeadSource::Referral->value,
            'status' => LeadStatus::Qualified->value,
            'owner_id' => $owner->getKey(),
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified()
    ;

    $actionLog = $lead->refresh()->actionLogs->sortByDesc('id')->first();

    if (! $actionLog instanceof ActionLog) {
        throw new RuntimeException('Expected a lead action log to be created.');
    }

    expect($actionLog->type)->toBe(ActionLogType::System)
        ->and($actionLog->title)->toBe('Lead details updated')
        ->and($actionLog->actor_id)->toBe($user->getKey())
        ->and($actionLog->body)->toBe(implode("\n", [
            'Name: Northwind Prospect -> Northwind Qualified Prospect',
            'Phone: +49 30 123456 -> +49 30 987654',
            'Source: Cold outreach -> Referral',
            'Status: Contacted -> Qualified',
            'Owner: - -> Jamie Fox',
        ]))
    ;
});

it('does not create a lead system timeline entry when nothing changed', function (): void {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Northwind Prospect',
        'email' => 'northwind@example.com',
        'company' => 'Northwind GmbH',
        'street' => 'Unter den Linden 1',
        'city' => 'Berlin',
        'state' => 'Berlin',
        'zip' => '10117',
        'country' => 'Germany',
        'phone' => '+49 30 123456',
        'source' => LeadSource::Referral->value,
        'status' => LeadStatus::Qualified->value,
    ]);

    actingAs($user);

    livewire(EditLead::class, ['record' => $lead->getKey()])
        ->fillForm([
            'name' => 'Northwind Prospect',
            'email' => 'northwind@example.com',
            'company' => 'Northwind GmbH',
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
            'state' => 'Berlin',
            'zip' => '10117',
            'country' => 'Germany',
            'phone' => '+49 30 123456',
            'source' => LeadSource::Referral->value,
            'status' => LeadStatus::Qualified->value,
            'owner_id' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified()
    ;

    expect($lead->refresh()->actionLogs)->toHaveCount(0);
});

it('allows an authenticated user to view a soft deleted lead with enum labels', function (): void {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Acme Prospect',
        'email' => 'acme@example.com',
        'company' => 'Acme Inc.',
        'phone' => '+49 30 123456',
        'source' => LeadSource::ColdOutreach->value,
        'status' => LeadStatus::Qualified->value,
    ]);

    $lead->delete();

    actingAs($user);

    get(LeadResource::getUrl('view', ['record' => $lead]))
        ->assertOk()
        ->assertSeeText('Acme Prospect')
        ->assertSeeText('acme@example.com')
        ->assertSeeText('Acme Inc.')
        ->assertSeeText('Cold outreach')
        ->assertSeeText('Qualified')
    ;
});

it('allows an authenticated user to add a note from the lead view page', function (): void {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Northwind Prospect',
        'email' => 'northwind@example.com',
        'source' => LeadSource::Referral->value,
        'status' => LeadStatus::New->value,
    ]);

    actingAs($user);

    livewire(ViewLead::class, ['record' => $lead->getKey()])
        ->callAction('addNote', [
            'title' => 'Discovery call',
            'body' => '',
        ])
        ->assertHasNoErrors()
        ->assertNotified()
    ;

    assertDatabaseHas(ActionLog::class, [
        'type' => ActionLogType::Note->value,
        'title' => 'Discovery call',
        'body' => null,
        'actionable_type' => Lead::class,
        'actionable_id' => $lead->getKey(),
        'actor_id' => $user->getKey(),
    ]);
});

it('allows an authenticated user to edit an action log from the lead view page', function (): void {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Northwind Prospect',
        'email' => 'northwind@example.com',
        'source' => LeadSource::Referral->value,
        'status' => LeadStatus::New->value,
    ]);

    $actionLog = $lead->actionLogs()->create([
        'type' => ActionLogType::Note,
        'title' => 'Discovery call',
        'body' => 'Discuss budget on the first call.',
        'actor_id' => $user->getKey(),
    ]);

    actingAs($user);

    livewire(ViewLead::class, ['record' => $lead->getKey()])
        ->callAction(
            'editActionLog',
            [
                'title' => 'Discovery call moved',
                'body' => 'Rescheduled to Thursday afternoon.',
            ],
            [
                'actionLog' => $actionLog->getKey(),
            ],
        )
        ->assertHasNoFormErrors()
        ->assertNotified()
    ;

    assertDatabaseHas(ActionLog::class, [
        'id' => $actionLog->getKey(),
        'type' => ActionLogType::Note->value,
        'title' => 'Discovery call moved',
        'body' => 'Rescheduled to Thursday afternoon.',
        'actionable_type' => Lead::class,
        'actionable_id' => $lead->getKey(),
        'actor_id' => $user->getKey(),
    ]);
});

it('allows an authenticated user to delete an action log from the lead view page', function (): void {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Northwind Prospect',
        'email' => 'northwind@example.com',
        'source' => LeadSource::Referral->value,
        'status' => LeadStatus::New->value,
    ]);

    $actionLog = $lead->actionLogs()->create([
        'type' => ActionLogType::Note,
        'title' => 'Discovery call',
        'body' => 'Discuss budget on the first call.',
        'actor_id' => $user->getKey(),
    ]);

    actingAs($user);

    livewire(ViewLead::class, ['record' => $lead->getKey()])
        ->callAction(
            'deleteActionLog',
            [],
            [
                'actionLog' => $actionLog->getKey(),
            ],
        )
        ->assertNotified()
    ;

    $deletedActionLog = ActionLog::withTrashed()
        ->whereKey($actionLog->getKey())
        ->firstOrFail();

    expect(ActionLog::query()->find($actionLog->getKey()))->toBeNull()
        ->and($deletedActionLog->trashed())->toBeTrue()
    ;
});

it('shows lead system timeline entries as read only and rejects managing them', function (): void {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Northwind Prospect',
        'email' => 'northwind@example.com',
        'source' => LeadSource::Referral->value,
        'status' => LeadStatus::New->value,
    ]);

    $actionLog = $lead->actionLogs()->create([
        'type' => ActionLogType::System,
        'title' => 'Lead details updated',
        'body' => 'Status: New -> Qualified',
    ]);
    $actionLogKey = $actionLog->getKey();

    if (! is_int($actionLogKey) && ! is_string($actionLogKey)) {
        throw new RuntimeException('Expected the lead action log key to be a string or int.');
    }

    actingAs($user);

    get(LeadResource::getUrl('view', ['record' => $lead]))
        ->assertOk()
        ->assertSeeText('Lead details updated')
        ->assertDontSee("mountAction('editActionLog', { actionLog: {$actionLogKey} })", false)
        ->assertDontSee("mountAction('deleteActionLog', { actionLog: {$actionLogKey} })", false)
    ;

    livewire(ViewLead::class, ['record' => $lead->getKey()])
        ->callAction(
            'editActionLog',
            [
                'title' => 'Blocked update',
                'body' => 'This should not be allowed.',
            ],
            [
                'actionLog' => $actionLog->getKey(),
            ],
        )
        ->assertNotified(Localization::translate('messages.notifications.action_log_not_modified'))
    ;

    assertDatabaseHas(ActionLog::class, [
        'id' => $actionLog->getKey(),
        'type' => ActionLogType::System->value,
        'title' => 'Lead details updated',
        'body' => 'Status: New -> Qualified',
        'actionable_type' => Lead::class,
        'actionable_id' => $lead->getKey(),
    ]);

    livewire(ViewLead::class, ['record' => $lead->getKey()])
        ->callAction(
            'deleteActionLog',
            [],
            [
                'actionLog' => $actionLog->getKey(),
            ],
        )
        ->assertNotified(Localization::translate('messages.notifications.action_log_not_modified'))
    ;

    assertDatabaseHas(ActionLog::class, [
        'id' => $actionLog->getKey(),
        'type' => ActionLogType::System->value,
        'title' => 'Lead details updated',
        'body' => 'Status: New -> Qualified',
        'actionable_type' => Lead::class,
        'actionable_id' => $lead->getKey(),
    ]);
});

it('renders translated lead UI in german', function (): void {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'name' => 'Northwind Prospect',
        'email' => 'northwind@example.com',
        'source' => LeadSource::ColdOutreach->value,
        'status' => LeadStatus::Qualified->value,
    ]);

    actingAs($user);

    withSession(['locale' => 'de'])
        ->get(LeadResource::getUrl('view', ['record' => $lead]))
        ->assertOk()
        ->assertSeeText('Kaltakquise')
        ->assertSeeText('Qualifiziert')
        ->assertSeeText('Notiz hinzufügen')
    ;
});
