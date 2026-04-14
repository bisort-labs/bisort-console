<?php

declare(strict_types=1);

namespace App\Support\ActionLogs;

use App\Enums\ActionLogType;
use App\Models\ActionLog;
use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerActionLogManager
{
    public function createNote(Customer $customer, string $title, ?string $body, int|string|null $actorId): void
    {
        $customer->actionLogs()->create([
            'type' => ActionLogType::Note,
            'title' => $title,
            'body' => $body,
            'actor_id' => filter_var($actorId, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
        ]);
    }

    public function update(ActionLog $actionLog, string $title, ?string $body): void
    {
        $actionLog->update([
            'title' => $title,
            'body' => $body,
        ]);
    }

    public function delete(ActionLog $actionLog): void
    {
        $actionLog->delete();
    }

    public function resolveForCustomer(Customer $customer, int|string|null $actionLogId): ActionLog
    {
        $normalizedActionLogId = $this->normalizeActionLogId($actionLogId);

        $actionLog = $customer
            ->loadMissing('actionLogs')
            ->actionLogs
            ->first(static fn (ActionLog $actionLog): bool => $actionLog->id === $normalizedActionLogId)
        ;

        if (! $actionLog instanceof ActionLog) {
            throw (new ModelNotFoundException())->setModel(ActionLog::class, [$normalizedActionLogId]);
        }

        return $actionLog;
    }

    private function normalizeActionLogId(int|string|null $actionLogId): int
    {
        if (! is_int($actionLogId) && (! is_string($actionLogId) || ! ctype_digit($actionLogId))) {
            throw (new ModelNotFoundException())->setModel(ActionLog::class);
        }

        return is_string($actionLogId) ? (int) $actionLogId : $actionLogId;
    }
}
