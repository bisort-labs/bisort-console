<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\Localization;
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
            self::Website => Localization::translate('fields.lead_sources.website'),
            self::Referral => Localization::translate('fields.lead_sources.referral'),
            self::ColdOutreach => Localization::translate('fields.lead_sources.cold_outreach'),
            self::LinkedIn => Localization::translate('fields.lead_sources.linked_in'),
            self::Facebook => Localization::translate('fields.lead_sources.facebook'),
            self::GooglePlus => Localization::translate('fields.lead_sources.google_plus'),
            self::Xing => Localization::translate('fields.lead_sources.xing'),
            self::YouTube => Localization::translate('fields.lead_sources.youtube'),
            self::Instagram => Localization::translate('fields.lead_sources.instagram'),
            self::XCom => Localization::translate('fields.lead_sources.x_com'),
            self::Other => Localization::translate('fields.lead_sources.other'),
        };
    }
}
