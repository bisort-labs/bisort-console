<?php

declare(strict_types=1);

namespace App\DTOs\ActionLog;

readonly class ActionLogSummary
{
    public function __construct(
        public string $title,
        public string $body,
    ) {
    }
}
