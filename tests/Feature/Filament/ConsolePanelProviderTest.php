<?php

declare(strict_types=1);

use App\Providers\Filament\ConsolePanelProvider;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Illuminate\Foundation\Vite;
use Illuminate\Session\Middleware\StartSession;

use function Safe\file_put_contents;
use function Safe\unlink;

it('skips theme assets while unit tests are running', function (): void {
    $provider = new ConsolePanelProvider(app());
    $method = new ReflectionMethod(ConsolePanelProvider::class, 'hasThemeAssets');

    expect($method->invoke($provider))->toBeFalse();
});

it('detects theme assets when the vite hot server is available outside tests', function (): void {
    app()->instance('env', 'local');

    $hotFile = public_path('test-hot');
    file_put_contents($hotFile, 'http://localhost:5173');

    try {
        $vite = (new Vite)->useHotFile($hotFile);
        app()->instance(Vite::class, $vite);

        $provider = new ConsolePanelProvider(app());
        $method = new ReflectionMethod(ConsolePanelProvider::class, 'hasThemeAssets');

        expect($method->invoke($provider))->toBeTrue();
    } finally {
        if (file_exists($hotFile)) {
            unlink($hotFile);
        }
    }
});

it('applies the vite theme when theme assets are available', function (): void {
    app()->instance('env', 'local');

    $hotFile = public_path('test-hot');
    file_put_contents($hotFile, 'http://localhost:5173');

    try {
        $vite = (new Vite)->useHotFile($hotFile);
        app()->instance(Vite::class, $vite);

        $provider = new ConsolePanelProvider(app());
        $method = new ReflectionMethod(ConsolePanelProvider::class, 'configureTheme');
        $panel = app(Panel::class);

        expect($method->invoke($provider, $panel))->toBeInstanceOf(Panel::class);
    } finally {
        if (file_exists($hotFile)) {
            unlink($hotFile);
        }
    }
});

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
