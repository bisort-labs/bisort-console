<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\Localization;
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
            self::New => Localization::translate('fields.lead_statuses.new'),
            self::Contacted => Localization::translate('fields.lead_statuses.contacted'),
            self::Qualified => Localization::translate('fields.lead_statuses.qualified'),
            self::Unqualified => Localization::translate('fields.lead_statuses.unqualified'),
        };
    }
}
