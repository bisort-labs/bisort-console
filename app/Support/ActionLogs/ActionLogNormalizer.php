<?php

declare(strict_types=1);

namespace App\Support\ActionLogs;

use App\Models\ActionLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActionLogNormalizer
{
    public function modelKey(Model $model): int|string
    {
        $key = $model->getKey();

        if (is_int($key) || is_string($key)) {
            return $key;
        }

        throw (new ModelNotFoundException())->setModel(ActionLog::class);
    }

    public function normalizeActionLogId(int|string|null $actionLogId): int
    {
        if (! is_int($actionLogId) && (! is_string($actionLogId) || ! ctype_digit($actionLogId))) {
            throw (new ModelNotFoundException())->setModel(ActionLog::class);
        }

        return is_string($actionLogId) ? (int) $actionLogId : $actionLogId;
    }

    public function normalizeActorId(int|string|null $actorId): ?int
    {
        return filter_var($actorId, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    }
}
