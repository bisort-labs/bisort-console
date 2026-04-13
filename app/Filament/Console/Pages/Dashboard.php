<?php

declare(strict_types=1);

namespace App\Filament\Console\Pages;

use App\Services\Localization;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class Dashboard extends BaseDashboard
{
    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return Localization::translate('navigation.groups.overview');
    }

    #[Override]
    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return Heroicon::OutlinedHomeModern;
    }
}
