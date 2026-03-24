<?php

declare(strict_types=1);

use App\Filament\Resources\ClientProjects\ClientProjectResource;
use App\Filament\Resources\ClientProjects\Pages\CreateClientProject;
use App\Filament\Resources\ClientProjects\Pages\ListClientProjects;
use App\Models\ClientProject;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
});

it('creates a client project via the Filament form', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(CreateClientProject::class)
        ->fillForm([
            'name' => 'Northwind Website Refresh',
            'description' => 'A redesign and rebuild of the client-facing marketing site.',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect()
    ;

    assertDatabaseHas(ClientProject::class, [
        'name' => 'Northwind Website Refresh',
        'slug' => 'northwind-website-refresh',
        'description' => 'A redesign and rebuild of the client-facing marketing site.',
        'is_active' => true,
    ]);
});

it('generates the slug from the name', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(CreateClientProject::class)
        ->fillForm([
            'name' => 'Client Success Portal',
        ])
        ->assertFormSet([
            'name' => 'Client Success Portal',
            'slug' => 'client-success-portal',
            'is_active' => false,
        ])
    ;
});

it('requires the slug to be unique', function (): void {
    $user = User::factory()->create();

    ClientProject::query()->create([
        'name' => 'Operations Dashboard',
        'slug' => 'operations-dashboard',
        'description' => 'The existing internal operations workspace.',
        'is_active' => true,
    ]);

    actingAs($user);

    livewire(CreateClientProject::class)
        ->fillForm([
            'name' => 'Operations Dashboard',
            'description' => 'A duplicate project that should fail validation.',
            'is_active' => false,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'slug' => 'unique',
        ])
    ;
});

it('allows an authenticated user to access the list page', function (): void {
    $user = User::factory()->create();

    $projects = collect([
        ClientProject::query()->create([
            'name' => 'Acme Partner Portal',
            'slug' => 'acme-partner-portal',
            'description' => 'A partner-facing portal for order visibility.',
            'is_active' => true,
        ]),
        ClientProject::query()->create([
            'name' => 'Helios Billing Migration',
            'slug' => 'helios-billing-migration',
            'description' => 'Billing migration support for a long-running client account.',
            'is_active' => false,
        ]),
    ]);

    actingAs($user);

    get(ClientProjectResource::getUrl('index'))
        ->assertOk()
    ;

    livewire(ListClientProjects::class)
        ->assertCanSeeTableRecords($projects)
    ;
});
