<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Console\Pages\Dashboard;
use App\Http\Middleware\SetLocale;
use App\Support\Localization;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Vite;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Override;

class ConsolePanelProvider extends PanelProvider
{
    #[Override]
    public function panel(Panel $panel): Panel
    {
        $this->configureDiscovery($panel);
        $this->configureTheme($panel);
        $this->configureBasePanel($panel);

        return $panel;
    }

    private function configureDiscovery(Panel $panel): void
    {
        $panel
            ->id('console')
            ->path('console')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
        ;
    }

    private function configureTheme(Panel $panel): void
    {
        /** @var Vite $vite */
        $vite = app(Vite::class);

        $hasThemeAssets = ! app()->runningUnitTests() &&
            ($vite->isRunningHot() || is_file(public_path('build/manifest.json')));

        if (! $hasThemeAssets) {
            return;
        }

        $panel->viteTheme('resources/css/filament/console/theme.css');
    }

    private function configureBasePanel(Panel $panel): void
    {
        $panel
            ->default()
            ->login()
            ->colors(['primary' => Color::Emerald])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): View => view('filament.partials.locale-switcher'),
            )
            ->navigationGroups($this->getNavigationGroups())
            ->userMenuItems($this->getUserMenuItems())
            ->widgets($this->getWidgets())
            ->middleware($this->getMiddlewares())
            ->authMiddleware($this->getAuthMiddlewares())
        ;
    }

    /**
     * @return array<NavigationGroup>
     */
    private function getNavigationGroups(): array
    {
        return [
            NavigationGroup::make()
                ->label(fn (): string => Localization::translate('navigation.groups.overview'))
                ->collapsible(false),
            NavigationGroup::make()
                ->label(fn (): string => Localization::translate('navigation.groups.management'))
                ->collapsible(),
        ];
    }

    /**
     * @return array<Action>
     */
    private function getUserMenuItems(): array
    {
        return [
            $this->makeLocaleSwitcherAction('en'),
            $this->makeLocaleSwitcherAction('de'),
        ];
    }

    private function makeLocaleSwitcherAction(string $locale): Action
    {
        $actionName = $locale === 'en' ? 'switchLocaleEn' : 'switchLocaleDe';

        return Action::make($actionName)
            ->label(fn (): string => Localization::translate("common.locales.{$locale}"))
            ->color(fn (): string => app()->currentLocale() === $locale ? 'primary' : 'gray')
            ->disabled(fn (): bool => app()->currentLocale() === $locale)
            ->sort($locale === 'en' ? 10 : 11)
            ->url(fn (): string => route('locale.switch', ['locale' => $locale]))
            ->postToUrl()
        ;
    }

    /**
     * @return array<class-string<Widget>>
     */
    private function getWidgets(): array
    {
        return [
            AccountWidget::class,
            FilamentInfoWidget::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    private function getMiddlewares(): array
    {
        return [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            SetLocale::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            PreventRequestForgery::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    private function getAuthMiddlewares(): array
    {
        return [
            Authenticate::class,
        ];
    }
}
