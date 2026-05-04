<?php

declare(strict_types=1);

namespace App\Client\Domain\Enum;

enum LeadSource: string
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
}
