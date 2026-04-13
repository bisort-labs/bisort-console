<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\Localization;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Override;

enum DealStage: string implements HasColor, HasLabel
{
    case New = 'new';
    case Contacted = 'contacted';
    case ProposalSent = 'proposal_sent';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    #[Override]
    public function getColor(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Contacted => 'info',
            self::ProposalSent => 'warning',
            self::Negotiation => 'primary',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::New => Localization::translate('fields.deal_stages.new'),
            self::Contacted => Localization::translate('fields.deal_stages.contacted'),
            self::ProposalSent => Localization::translate('fields.deal_stages.proposal_sent'),
            self::Negotiation => Localization::translate('fields.deal_stages.negotiation'),
            self::Won => Localization::translate('fields.deal_stages.won'),
            self::Lost => Localization::translate('fields.deal_stages.lost'),
        };
    }
}
