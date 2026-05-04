<?php

declare(strict_types=1);

namespace App\Client\Domain\Enum;

enum DealStage: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case ProposalSent = 'proposal_sent';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';
}
