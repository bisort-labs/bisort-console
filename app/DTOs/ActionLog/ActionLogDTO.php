<?php

declare(strict_types=1);

namespace App\DTOs\ActionLog;

readonly class ActionLogDTO
{
    public function __construct(
        public string $title,
        public string $body,
        public string $happenedAt,
        public string $actorName,
    ) {}
}
