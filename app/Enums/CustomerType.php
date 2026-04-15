<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\Localization;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Override;

enum CustomerType: string implements HasColor, HasLabel
{
    case B2B = 'b2b';
    case B2C = 'b2c';

    #[Override]
    public function getColor(): string
    {
        return match ($this) {
            self::B2B => 'primary',
            self::B2C => 'info',
        };
    }

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::B2B => Localization::translate('fields.customer_types.b2b'),
            self::B2C => Localization::translate('fields.customer_types.b2c'),
        };
    }
}
