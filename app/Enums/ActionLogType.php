<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\Localization;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Override;

enum ActionLogType: string implements HasColor, HasLabel
{
    case Note = 'note';
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case System = 'system';

    #[Override]
    public function getColor(): string
    {
        return match ($this) {
            self::Note => 'gray',
            self::Call => 'info',
            self::Email => 'warning',
            self::Meeting => 'success',
            self::System => 'primary',
        };
    }

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::Note => Localization::translate('fields.action_log_types.note'),
            self::Call => Localization::translate('fields.action_log_types.call'),
            self::Email => Localization::translate('fields.action_log_types.email'),
            self::Meeting => Localization::translate('fields.action_log_types.meeting'),
            self::System => Localization::translate('fields.action_log_types.system'),
        };
    }
}
