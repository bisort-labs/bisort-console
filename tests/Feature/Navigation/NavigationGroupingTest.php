<?php

declare(strict_types=1);

use App\Filament\Console\Pages\Dashboard;
use App\Filament\Resources\ClientProjects\ClientProjectResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Support\Icons\Heroicon;

it('uses english navigation groups for the console sidebar', function (): void {
    app()->setLocale('en');

    expect(Dashboard::getNavigationGroup())->toBe('Overview')
        ->and(CustomerResource::getNavigationGroup())->toBe('Management')
        ->and(ClientProjectResource::getNavigationGroup())->toBe('Management')
        ->and(DealResource::getNavigationGroup())->toBe('Management')
        ->and(LeadResource::getNavigationGroup())->toBe('Management')
    ;
});

it('uses german navigation groups for the console sidebar', function (): void {
    app()->setLocale('de');

    expect(Dashboard::getNavigationGroup())->toBe('Übersicht')
        ->and(CustomerResource::getNavigationGroup())->toBe('Verwaltung')
        ->and(ClientProjectResource::getNavigationGroup())->toBe('Verwaltung')
        ->and(DealResource::getNavigationGroup())->toBe('Verwaltung')
        ->and(LeadResource::getNavigationGroup())->toBe('Verwaltung')
    ;
});

it('uses distinct navigation icons for the console sidebar', function (): void {
    expect(Dashboard::getNavigationIcon())->toBe(Heroicon::OutlinedHomeModern)
        ->and(CustomerResource::getNavigationIcon())->toBe(Heroicon::OutlinedBuildingOffice2)
        ->and(ClientProjectResource::getNavigationIcon())->toBe(Heroicon::OutlinedBriefcase)
        ->and(DealResource::getNavigationIcon())->toBe(Heroicon::OutlinedBanknotes)
        ->and(LeadResource::getNavigationIcon())->toBe(Heroicon::OutlinedUserGroup)
    ;
});

it('sorts leads, customers, client projects, and deals in the management navigation group', function (): void {
    $leadNavigationSort = LeadResource::getNavigationSort();
    $customerNavigationSort = CustomerResource::getNavigationSort();
    $clientProjectNavigationSort = ClientProjectResource::getNavigationSort();
    $dealNavigationSort = DealResource::getNavigationSort();

    if ($leadNavigationSort === null || $customerNavigationSort === null || $clientProjectNavigationSort === null || $dealNavigationSort === null) {
        throw new RuntimeException('Navigation sort must be configured for the management resources.');
    }

    expect($leadNavigationSort)->toBeLessThan($clientProjectNavigationSort)
        ->and($leadNavigationSort)->toBeLessThan($customerNavigationSort)
        ->and($customerNavigationSort)->toBeLessThan($clientProjectNavigationSort)
        ->and($clientProjectNavigationSort)->toBeLessThan($dealNavigationSort)
        ->and($leadNavigationSort)->toBe(10)
        ->and($customerNavigationSort)->toBe(15)
        ->and($clientProjectNavigationSort)->toBe(20)
        ->and($dealNavigationSort)->toBe(30)
    ;
});
