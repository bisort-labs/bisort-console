<?php

declare(strict_types=1);

use App\Filament\Console\Pages\Dashboard;
use App\Filament\Resources\ClientProjects\ClientProjectResource;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Support\Icons\Heroicon;

it('uses english navigation groups for the console sidebar', function (): void {
    app()->setLocale('en');

    expect(Dashboard::getNavigationGroup())->toBe('Overview')
        ->and(ClientProjectResource::getNavigationGroup())->toBe('Management')
        ->and(LeadResource::getNavigationGroup())->toBe('Management')
    ;
});

it('uses german navigation groups for the console sidebar', function (): void {
    app()->setLocale('de');

    expect(Dashboard::getNavigationGroup())->toBe('Übersicht')
        ->and(ClientProjectResource::getNavigationGroup())->toBe('Verwaltung')
        ->and(LeadResource::getNavigationGroup())->toBe('Verwaltung')
    ;
});

it('uses distinct navigation icons for the console sidebar', function (): void {
    expect(Dashboard::getNavigationIcon())->toBe(Heroicon::OutlinedHomeModern)
        ->and(ClientProjectResource::getNavigationIcon())->toBe(Heroicon::OutlinedBriefcase)
        ->and(LeadResource::getNavigationIcon())->toBe(Heroicon::OutlinedUserGroup)
    ;
});

it('sorts leads before client projects in the management navigation group', function (): void {
    $leadNavigationSort = LeadResource::getNavigationSort();
    $clientProjectNavigationSort = ClientProjectResource::getNavigationSort();

    if ($leadNavigationSort === null || $clientProjectNavigationSort === null) {
        throw new RuntimeException('Navigation sort must be configured for both resources.');
    }

    expect($leadNavigationSort)->toBeLessThan($clientProjectNavigationSort)
        ->and($leadNavigationSort)->toBe(10)
        ->and($clientProjectNavigationSort)->toBe(20)
    ;
});
