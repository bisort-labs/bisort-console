<?php

declare(strict_types=1);

use App\Enums\ActionLogType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Models\ActionLog;
use App\Models\Lead;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\withSession;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
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
