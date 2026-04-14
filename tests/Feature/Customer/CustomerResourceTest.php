<?php

declare(strict_types=1);

use App\Enums\ActionLogType;
use App\Enums\CustomerType;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\ActionLog;
use App\Models\Customer;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\withSession;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
});

it('creates a customer via the Filament form and logs a system action', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(CreateCustomer::class)
        ->fillForm([
            'name' => 'Northwind GmbH',
            'type' => CustomerType::B2B->value,
            'email' => 'billing@northwind.example',
            'phone' => '+49 30 123456',
            'country_code' => 'de',
            'vat_id' => 'DE123456789',
            'tax_number' => 'TAX-12345678',
            'is_vat_exempt' => false,
            'billing_address' => [
                'street' => 'Unter den Linden 1',
                'city' => 'Berlin',
                'state' => 'Berlin',
                'zip' => '10117',
                'country' => 'Germany',
            ],
            'payment_terms_days' => 14,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect()
    ;

    $customer = Customer::query()->firstOrFail();

    expect($customer->country_code)->toBe('DE');

    assertDatabaseHas(Customer::class, [
        'name' => 'Northwind GmbH',
        'type' => CustomerType::B2B->value,
        'email' => 'billing@northwind.example',
        'phone' => '+49 30 123456',
        'country_code' => 'DE',
        'vat_id' => 'DE123456789',
        'tax_number' => 'TAX-12345678',
        'is_vat_exempt' => false,
        'payment_terms_days' => 14,
    ]);

    assertDatabaseHas(ActionLog::class, [
        'type' => ActionLogType::System->value,
        'title' => 'Customer created',
        'actionable_type' => Customer::class,
        'actionable_id' => $customer->getKey(),
        'actor_id' => null,
    ]);
});

it('defaults the customer type to b2b', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(CreateCustomer::class)
        ->fillForm([
            'name' => 'Default Type Customer',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
    ;

    assertDatabaseHas(Customer::class, [
        'name' => 'Default Type Customer',
        'type' => CustomerType::B2B->value,
    ]);
});

it('allows an authenticated user to access the list page', function (): void {
    $user = User::factory()->create();

    $customers = collect([
        Customer::query()->create([
            'name' => 'Acme GmbH',
            'type' => CustomerType::B2B->value,
            'email' => 'accounts@acme.example',
        ]),
        Customer::query()->create([
            'name' => 'Alex Example',
            'type' => CustomerType::B2C->value,
            'email' => 'alex@example.test',
        ]),
    ]);

    actingAs($user);

    get(CustomerResource::getUrl('index'))
        ->assertOk()
    ;

    livewire(ListCustomers::class)
        ->assertCanSeeTableRecords($customers)
    ;
});

it('allows an authenticated user to access the edit page', function (): void {
    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Client Operations GmbH',
        'type' => CustomerType::B2B->value,
        'email' => 'finance@client-ops.example',
    ]);

    actingAs($user);

    get(CustomerResource::getUrl('edit', ['record' => $customer]))
        ->assertOk()
        ->assertSeeText('Save changes')
        ->assertSeeText('VAT ID')
        ->assertSeeText('Payment terms (days)')
    ;
});

it('allows an authenticated user to view a soft deleted customer with enum labels', function (): void {
    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Acme GmbH',
        'type' => CustomerType::B2B->value,
        'email' => 'accounts@acme.example',
        'billing_address' => [
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
            'country' => 'Germany',
        ],
    ]);

    $customer->delete();

    actingAs($user);

    get(CustomerResource::getUrl('view', ['record' => $customer]))
        ->assertOk()
        ->assertSeeText('Acme GmbH')
        ->assertSeeText('B2B')
        ->assertSeeText('Unter den Linden 1, Berlin, Germany')
    ;
});

it('updates a customer and logs a system action', function (): void {
    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Northwind GmbH',
        'type' => CustomerType::B2B->value,
        'country_code' => 'DE',
        'vat_id' => 'DE123',
        'billing_address' => [
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
            'country' => 'Germany',
        ],
    ]);

    actingAs($user);

    livewire(EditCustomer::class, ['record' => $customer->getKey()])
        ->fillForm([
            'name' => 'Northwind GmbH',
            'type' => CustomerType::B2B->value,
            'country_code' => 'FR',
            'vat_id' => 'FR999999999',
            'billing_address' => [
                'street' => '10 Rue de Rivoli',
                'city' => 'Paris',
                'country' => 'France',
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified()
    ;

    assertDatabaseHas(Customer::class, [
        'id' => $customer->getKey(),
        'country_code' => 'FR',
        'vat_id' => 'FR999999999',
    ]);

    assertDatabaseHas(ActionLog::class, [
        'type' => ActionLogType::System->value,
        'title' => 'Customer updated',
        'actionable_type' => Customer::class,
        'actionable_id' => $customer->getKey(),
        'actor_id' => null,
    ]);
});

it('allows an authenticated user to add a note from the customer view page', function (): void {
    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Northwind GmbH',
        'type' => CustomerType::B2B->value,
    ]);

    actingAs($user);

    livewire(ViewCustomer::class, ['record' => $customer->getKey()])
        ->callAction('addNote', [
            'title' => 'Sent billing setup request',
            'body' => '',
        ])
        ->assertHasNoErrors()
        ->assertNotified()
    ;

    assertDatabaseHas(ActionLog::class, [
        'type' => ActionLogType::Note->value,
        'title' => 'Sent billing setup request',
        'body' => null,
        'actionable_type' => Customer::class,
        'actionable_id' => $customer->getKey(),
        'actor_id' => $user->getKey(),
    ]);
});

it('allows an authenticated user to edit an action log from the customer view page', function (): void {
    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Northwind GmbH',
        'type' => CustomerType::B2B->value,
    ]);

    $actionLog = $customer->actionLogs()->create([
        'type' => ActionLogType::Note,
        'title' => 'Sent billing setup request',
        'body' => 'Waiting for finance documents.',
        'actor_id' => $user->getKey(),
    ]);

    actingAs($user);

    livewire(ViewCustomer::class, ['record' => $customer->getKey()])
        ->callAction(
            'editActionLog',
            [
                'title' => 'Billing setup scheduled',
                'body' => 'Finance documents arrived and setup is scheduled.',
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
        'title' => 'Billing setup scheduled',
        'body' => 'Finance documents arrived and setup is scheduled.',
        'actionable_type' => Customer::class,
        'actionable_id' => $customer->getKey(),
        'actor_id' => $user->getKey(),
    ]);
});

it('allows an authenticated user to delete an action log from the customer view page', function (): void {
    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Northwind GmbH',
        'type' => CustomerType::B2B->value,
    ]);

    $actionLog = $customer->actionLogs()->create([
        'type' => ActionLogType::Note,
        'title' => 'Sent billing setup request',
        'body' => 'Waiting for finance documents.',
        'actor_id' => $user->getKey(),
    ]);

    actingAs($user);

    livewire(ViewCustomer::class, ['record' => $customer->getKey()])
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

it('renders customer timeline entries ordered by happened at descending', function (): void {
    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Northwind GmbH',
        'type' => CustomerType::B2B->value,
    ]);

    $customer->actionLogs()->create([
        'type' => ActionLogType::Note,
        'title' => 'Earlier note',
        'body' => 'First timeline entry.',
        'actor_id' => $user->getKey(),
        'happened_at' => '2026-04-10 09:00:00',
    ]);

    $customer->actionLogs()->create([
        'type' => ActionLogType::Note,
        'title' => 'Later note',
        'body' => 'Second timeline entry.',
        'actor_id' => $user->getKey(),
        'happened_at' => '2026-04-11 09:00:00',
    ]);

    actingAs($user);

    get(CustomerResource::getUrl('view', ['record' => $customer]))
        ->assertOk()
        ->assertSeeInOrder([
            'Later note',
            'Earlier note',
        ])
    ;
});

it('renders translated customer UI in german', function (): void {
    $user = User::factory()->create();

    $customer = Customer::query()->create([
        'name' => 'Northwind GmbH',
        'type' => CustomerType::B2B->value,
    ]);

    actingAs($user);

    withSession(['locale' => 'de'])
        ->get(CustomerResource::getUrl('view', ['record' => $customer]))
        ->assertOk()
        ->assertSeeText('Kunden')
        ->assertSeeText('Notiz hinzufügen')
        ->assertSeeText('Rechnungsadresse')
    ;
});
