<?php

declare(strict_types=1);

namespace App\Enums;

enum ActionLogType: string
{
    case Note = 'note';
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case System = 'system';
}
