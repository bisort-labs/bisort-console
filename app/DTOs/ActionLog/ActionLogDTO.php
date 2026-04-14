<?php

declare(strict_types=1);

namespace App\DTOs\ActionLog;

readonly class ActionLogDTO
{
    public function __construct(
        public int $id,
        public string $typeLabel,
        public string $typeColor,
        public string $title,
        public string $body,
        public string $happenedAt,
        public string $actorName,
        public bool $canManage,
    ) {
    }
}
