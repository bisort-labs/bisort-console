<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Override;

enum LeadSource: string implements HasColor, HasLabel
{
    case Website = 'website';
    case Referral = 'referral';
    case ColdOutreach = 'cold_outreach';
    case LinkedIn = 'linked_in';
    case Facebook = 'facebook';
    case GooglePlus = 'google_plus';
    case Xing = 'xing';
    case YouTube = 'youtube';
    case Instagram = 'instagram';
    case XCom = 'x.com';
    case Other = 'other';

    #[Override]
    public function getColor(): string
    {
        return match ($this) {
            self::Website => 'primary',
            self::Referral => 'success',
            self::ColdOutreach => 'warning',
            self::LinkedIn,
            self::Facebook,
            self::Instagram,
            self::Xing,
            self::Other => 'gray',
            self::YouTube => 'info',
            self::XCom => 'dark',
            self::GooglePlus => 'danger',
        };
    }

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::Website => 'Website',
            self::Referral => 'Referral',
            self::ColdOutreach => 'Cold outreach',
            self::LinkedIn => 'LinkedIn',
            self::Facebook => 'Facebook',
            self::GooglePlus => 'Google+',
            self::Xing => 'Xing',
            self::YouTube => 'YouTube',
            self::Instagram => 'Instagram',
            self::XCom => 'X.com',
            self::Other => 'Other',
        };
    }
}
