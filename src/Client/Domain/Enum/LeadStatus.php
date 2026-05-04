<?php

declare(strict_types=1);

namespace App\Client\Domain\Enum;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Unqualified = 'unqualified';
}
