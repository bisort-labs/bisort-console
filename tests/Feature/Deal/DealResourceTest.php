<?php

declare(strict_types=1);

use App\Enums\ActionLogType;
use App\Enums\DealStage;
use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\Deals\Pages\CreateDeal;
use App\Filament\Resources\Deals\Pages\EditDeal;
use App\Filament\Resources\Deals\Pages\ListDeals;
use App\Filament\Resources\Deals\Pages\ViewDeal;
use App\Mappers\ActionLogTimelineMapper;
use App\Models\ActionLog;
use App\Models\ClientProject;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use App\Support\Deals\DealMoney;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\withSession;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
});

it('creates a deal via the Filament form and stores the value in cents', function (): void {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();

    actingAs($user);

    livewire(CreateDeal::class)
        ->fillForm([
            'lead_id' => $lead->getKey(),
            'title' => 'Website redesign + maintenance',
            'expected_value_cents' => '1200.50',
            'currency' => 'eur',
            'probability' => 55,
            'close_date' => '2026-06-15',
            'notes' => 'Client asked for phased delivery.',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect()
    ;

    assertDatabaseHas(Deal::class, [
        'lead_id' => $lead->getKey(),
        'title' => 'Website redesign + maintenance',
        'stage' => DealStage::New->value,
        'expected_value_cents' => 120050,
        'currency' => 'EUR',
        'probability' => 55,
        'close_date' => '2026-06-15 00:00:00',
        'notes' => 'Client asked for phased delivery.',
    ]);
});

it('requires a lost reason when a deal is marked as lost', function (): void {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();

    actingAs($user);

    livewire(CreateDeal::class)
        ->fillForm([
            'lead_id' => $lead->getKey(),
            'title' => 'Operations rollout',
            'stage' => DealStage::Lost->value,
            'expected_value_cents' => '4000.00',
            'currency' => 'EUR',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'lost_reason' => 'required',
        ])
    ;
});

it('updates a deal and allows linking a project when won', function (): void {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $lead = Lead::factory()->create();
    $project = ClientProject::query()->create([
        'name' => 'Northwind Website Refresh',
        'slug' => 'northwind-website-refresh',
        'description' => 'A redesign and rebuild of the public marketing site.',
        'is_active' => true,
    ]);

    $deal = Deal::factory()->for($lead)->create([
        'title' => 'Website redesign + maintenance',
        'stage' => DealStage::ProposalSent,
        'expected_value_cents' => 120050,
        'currency' => 'EUR',
        'probability' => 55,
        'project_id' => null,
        'owner_id' => null,
    ]);

    actingAs($user);

    livewire(EditDeal::class, ['record' => $deal->getKey()])
        ->fillForm([
            'lead_id' => $lead->getKey(),
            'title' => 'Website redesign + maintenance',
            'stage' => DealStage::Won->value,
            'expected_value_cents' => '1200.50',
            'currency' => 'EUR',
            'probability' => 90,
            'close_date' => '2026-06-30',
            'owner_id' => $owner->getKey(),
            'project_id' => $project->getKey(),
            'notes' => 'Signed and ready for kickoff.',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified()
    ;

    assertDatabaseHas(Deal::class, [
        'id' => $deal->getKey(),
        'stage' => DealStage::Won->value,
        'project_id' => $project->getKey(),
        'owner_id' => $owner->getKey(),
        'probability' => 90,
        'close_date' => '2026-06-30 00:00:00',
        'notes' => 'Signed and ready for kickoff.',
    ]);
});

it('hydrates stored deal cents back to a decimal amount in the edit form', function (): void {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();
    $deal = Deal::factory()->for($lead)->create([
        'expected_value_cents' => 120050,
    ]);

    actingAs($user);

    livewire(EditDeal::class, ['record' => $deal->getKey()])
        ->assertFormSet([
            'expected_value_cents' => DealMoney::centsToAmount($deal->expected_value_cents),
        ])
    ;
});

it('allows an authenticated user to access the deals list page', function (): void {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();

    $deals = Deal::factory(2)->for($lead)->create();

    actingAs($user);

    get(DealResource::getUrl('index'))
        ->assertOk()
    ;

    livewire(ListDeals::class)
        ->assertCanSeeTableRecords($deals)
    ;
});

it('allows an authenticated user to view a soft deleted deal', function (): void {
    $user = User::factory()->create();
    $lead = Lead::factory()->create([
        'name' => 'Northwind Prospect',
    ]);
    $deal = Deal::factory()->for($lead)->create([
        'title' => 'Website redesign + maintenance',
        'stage' => DealStage::Negotiation,
    ]);

    $deal->delete();

    actingAs($user);

    get(DealResource::getUrl('view', ['record' => $deal]))
        ->assertOk()
        ->assertSeeText('Overview')
        ->assertSeeText('History')
        ->assertSeeText('Website redesign + maintenance')
        ->assertSeeText('Northwind Prospect')
    ;
});

it('allows an authenticated user to add a note from the deal view page', function (): void {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();
    $deal = Deal::factory()->for($lead)->create();

    actingAs($user);

    livewire(ViewDeal::class, ['record' => $deal->getKey()])
        ->callAction('addNote', [
            'title' => 'Discovery recap',
            'body' => 'Budget approved for the first phase.',
        ])
        ->assertHasNoErrors()
        ->assertNotified()
    ;

    assertDatabaseHas(ActionLog::class, [
        'type' => ActionLogType::Note->value,
        'title' => 'Discovery recap',
        'body' => 'Budget approved for the first phase.',
        'actionable_type' => Deal::class,
        'actionable_id' => $deal->getKey(),
        'actor_id' => $user->getKey(),
    ]);
});

it('allows an authenticated user to edit and delete a deal action log from the view page', function (): void {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();
    $deal = Deal::factory()->for($lead)->create();

    $actionLog = $deal->actionLogs()->create([
        'type' => ActionLogType::Note,
        'title' => 'Discovery recap',
        'body' => 'Need final pricing sign-off.',
        'actor_id' => $user->getKey(),
    ]);

    actingAs($user);

    livewire(ViewDeal::class, ['record' => $deal->getKey()])
        ->callAction(
            'editActionLog',
            [
                'title' => 'Discovery recap updated',
                'body' => 'Pricing approved and kickoff scheduled.',
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
        'title' => 'Discovery recap updated',
        'body' => 'Pricing approved and kickoff scheduled.',
        'actionable_type' => Deal::class,
        'actionable_id' => $deal->getKey(),
    ]);

    livewire(ViewDeal::class, ['record' => $deal->getKey()])
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

it('orders deal timeline actions with the most recent entry first', function (): void {
    $lead = Lead::factory()->create();
    $deal = Deal::factory()->for($lead)->create();

    $olderActionLog = $deal->actionLogs()->create([
        'type' => ActionLogType::Email,
        'title' => 'Sent proposal',
        'body' => 'The client requested a cost breakdown.',
        'happened_at' => '2026-04-01 10:00:00',
    ]);

    $recentActionLog = $deal->actionLogs()->create([
        'type' => ActionLogType::Note,
        'title' => 'Follow up booked',
        'body' => 'Decision meeting scheduled for Friday.',
        'happened_at' => '2026-04-03 15:00:00',
    ]);

    $timelineActions = ActionLogTimelineMapper::map($deal->actionLogs()->get());

    expect($timelineActions->pluck('id')->all())->toBe([
        $recentActionLog->getKey(),
        $olderActionLog->getKey(),
    ]);
});

it('renders translated deal UI in german', function (): void {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();
    $deal = Deal::factory()->for($lead)->create([
        'stage' => DealStage::ProposalSent,
        'lost_reason' => null,
    ]);

    actingAs($user);

    withSession(['locale' => 'de'])
        ->get(DealResource::getUrl('view', ['record' => $deal]))
        ->assertOk()
        ->assertSeeText('Deals')
        ->assertSeeText('Phase')
        ->assertSeeText('Angebot gesendet')
        ->assertSeeText('Notiz hinzufügen')
    ;
});
