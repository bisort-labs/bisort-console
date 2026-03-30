<?php

declare(strict_types=1);

use App\Providers\Filament\ConsolePanelProvider;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationGroup;
use Illuminate\Session\Middleware\StartSession;

it('builds navigation groups, locale actions, and middleware stacks', function (): void {
    $provider = new ConsolePanelProvider(app());

    $navigationGroupsMethod = new ReflectionMethod(ConsolePanelProvider::class, 'getNavigationGroups');
    /** @var array<int, NavigationGroup> $navigationGroups */
    $navigationGroups = $navigationGroupsMethod->invoke($provider);

    $userMenuItemsMethod = new ReflectionMethod(ConsolePanelProvider::class, 'getUserMenuItems');
    /** @var array<int, Action> $userMenuItems */
    $userMenuItems = $userMenuItemsMethod->invoke($provider);

    $middlewaresMethod = new ReflectionMethod(ConsolePanelProvider::class, 'getMiddlewares');
    $authMiddlewaresMethod = new ReflectionMethod(ConsolePanelProvider::class, 'getAuthMiddlewares');

    $actionNames = array_map(static function (Action $action): string {
        $name = $action->getName();

        if (is_string($name)) {
            return $name;
        }

        throw new RuntimeException('Expected locale actions to have string names.');
    }, $userMenuItems);

    expect($navigationGroups)->toHaveCount(2)
        ->and($actionNames)->toBe([
            'switchLocaleEn',
            'switchLocaleDe',
        ])
        ->and($middlewaresMethod->invoke($provider))->toContain(StartSession::class)
        ->and($authMiddlewaresMethod->invoke($provider))->toContain(Authenticate::class)
    ;
});
