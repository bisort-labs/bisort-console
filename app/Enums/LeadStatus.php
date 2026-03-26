<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Override;

enum LeadStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Unqualified = 'unqualified';

    #[Override]
    public function getColor(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Contacted => 'info',
            self::Qualified => 'success',
            self::Unqualified => 'danger',
        };
    }

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::Unqualified => 'Unqualified',
        };
    }
}
