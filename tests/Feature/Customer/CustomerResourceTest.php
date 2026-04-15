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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
});

it('creates a customer via the Filament form with the default type and system action', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(CreateCustomer::class)
        ->fillForm([
            'name' => 'Northwind GmbH',
            'email' => 'billing@northwind.example',
            'phone' => '+49 30 123456',
            'country_code' => 'de',
            'billing_address.street' => 'Unter den Linden 1',
            'billing_address.city' => 'Berlin',
            'billing_address.state' => 'Berlin',
            'billing_address.zip' => '10117',
            'billing_address.country' => 'Germany',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect()
    ;

    assertDatabaseHas(Customer::class, [
        'name' => 'Northwind GmbH',
        'type' => CustomerType::B2B->value,
        'email' => 'billing@northwind.example',
        'phone' => '+49 30 123456',
        'country_code' => 'DE',
    ]);

    $customer = Customer::query()->firstWhere('email', 'billing@northwind.example');

    if (! $customer instanceof Customer) {
        throw new RuntimeException('Expected the created customer to exist.');
    }

    $actionLog = $customer->refresh()->actionLogs->sortByDesc('id')->first();

    if (! $actionLog instanceof ActionLog) {
        throw new RuntimeException('Expected a customer creation action log to be created.');
    }

    expect($customer->type)->toBe(CustomerType::B2B)
        ->and($customer->billing_address)->toBe([
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
            'state' => 'Berlin',
            'zip' => '10117',
            'country' => 'Germany',
        ])
        ->and($actionLog->type)->toBe(ActionLogType::System)
        ->and($actionLog->title)->toBe('Customer created')
        ->and($actionLog->body)->toBe('Customer record was created.')
        ->and($actionLog->actor_id)->toBe($user->getKey())
    ;
});

it('requires a vat exemption reason when a customer is vat exempt', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(CreateCustomer::class)
        ->fillForm([
            'name' => 'Northwind GmbH',
            'is_vat_exempt' => true,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'vat_exemption_reason' => 'required',
        ])
    ;
});

it('allows an authenticated user to access the customer list page', function (): void {
    $user = User::factory()->create();

    $customers = new EloquentCollection();

    Customer::withoutEvents(function () use (&$customers): void {
        $customers = Customer::factory(2)->create();
    });

    actingAs($user);

    get(CustomerResource::getUrl('index'))
        ->assertOk()
    ;

    livewire(ListCustomers::class)
        ->assertCanSeeTableRecords($customers)
    ;
});

it('updates customer tax and billing fields and logs a summarized system action', function (): void {
    $user = User::factory()->create(['name' => 'Morgan Lee']);

    $customer = null;

    Customer::withoutEvents(function () use (&$customer): void {
        $customer = Customer::query()->create([
            'name' => 'Northwind GmbH',
            'type' => CustomerType::B2B->value,
            'email' => 'billing@northwind.example',
            'phone' => '+49 30 123456',
            'country_code' => 'DE',
            'vat_id' => null,
            'tax_number' => null,
            'is_vat_exempt' => false,
            'vat_exemption_reason' => null,
            'billing_address' => [
                'street' => 'Unter den Linden 1',
                'city' => 'Berlin',
                'state' => 'Berlin',
                'zip' => '10117',
                'country' => 'Germany',
            ],
        ]);
    });

    if (! $customer instanceof Customer) {
        throw new RuntimeException('Expected a customer instance.');
    }

    actingAs($user);

    livewire(EditCustomer::class, ['record' => $customer->getKey()])
        ->fillForm([
            'name' => 'Northwind GmbH',
            'type' => CustomerType::B2B->value,
            'email' => 'billing@northwind.example',
            'phone' => '+49 30 123456',
            'country_code' => 'AT',
            'vat_id' => 'ATU12345678',
            'tax_number' => 'TAX-123',
            'is_vat_exempt' => true,
            'vat_exemption_reason' => 'Reverse charge',
            'billing_address.street' => 'Kärntner Ring 1',
            'billing_address.city' => 'Vienna',
            'billing_address.state' => 'Vienna',
            'billing_address.zip' => '1010',
            'billing_address.country' => 'Austria',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified()
    ;

    assertDatabaseHas(Customer::class, [
        'id' => $customer->getKey(),
        'country_code' => 'AT',
        'vat_id' => 'ATU12345678',
        'tax_number' => 'TAX-123',
        'is_vat_exempt' => 1,
        'vat_exemption_reason' => 'Reverse charge',
    ]);

    $actionLog = $customer->refresh()->actionLogs->sortByDesc('id')->first();

    if (! $actionLog instanceof ActionLog) {
        throw new RuntimeException('Expected a customer update action log to be created.');
    }

    expect($customer->billing_address)->toBe([
        'street' => 'Kärntner Ring 1',
        'city' => 'Vienna',
        'state' => 'Vienna',
        'zip' => '1010',
        'country' => 'Austria',
    ])
        ->and($actionLog->type)->toBe(ActionLogType::System)
        ->and($actionLog->title)->toBe('Customer details updated')
        ->and($actionLog->actor_id)->toBe($user->getKey())
        ->and($actionLog->body)->toBe(implode("\n", [
            'Country code: DE -> AT',
            'VAT ID: - -> ATU12345678',
            'Tax number: - -> TAX-123',
            'VAT exempt: No -> Yes',
            'VAT exemption reason: - -> Reverse charge',
            'Billing address: Unter den Linden 1, Berlin, Berlin, 10117, Germany -> Kärntner Ring 1, Vienna, Vienna, 1010, Austria',
        ]))
    ;
});

it('allows an authenticated user to add a note from the customer view page', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    actingAs($user);

    livewire(ViewCustomer::class, ['record' => $customer->getKey()])
        ->callAction('addNote', [
            'title' => 'Billing call',
            'body' => 'Discussed updated VAT handling.',
        ])
        ->assertHasNoErrors()
        ->assertNotified()
    ;

    assertDatabaseHas(ActionLog::class, [
        'actionable_type' => Customer::class,
        'actionable_id' => $customer->getKey(),
        'type' => ActionLogType::Note->value,
        'title' => 'Billing call',
        'body' => 'Discussed updated VAT handling.',
        'actor_id' => $user->getKey(),
    ]);
});

it('shows customer timeline entries with the newest action first', function (): void {
    $user = User::factory()->create();

    $customer = null;

    Customer::withoutEvents(function () use (&$customer): void {
        $customer = Customer::factory()->create();
    });

    if (! $customer instanceof Customer) {
        throw new RuntimeException('Expected a customer instance.');
    }

    $customer->actionLogs()->create([
        'type' => ActionLogType::Note,
        'title' => 'Older note',
        'body' => 'The older timeline entry.',
        'happened_at' => Carbon::parse('2026-04-10 08:00:00'),
    ]);

    $customer->actionLogs()->create([
        'type' => ActionLogType::System,
        'title' => 'Newest note',
        'body' => 'The newer timeline entry.',
        'happened_at' => Carbon::parse('2026-04-12 12:00:00'),
    ]);

    actingAs($user);

    get(CustomerResource::getUrl('view', ['record' => $customer]))
        ->assertOk()
        ->assertSeeInOrder([
            'Newest note',
            'Older note',
        ])
    ;
});
