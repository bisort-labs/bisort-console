<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\DTOs\ActionLog\ActionLogSummary;
use App\Enums\ActionLogType;
use App\Models\ActionLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

readonly class ActionLogManager
{
    public function __construct(
        private ActionLogNormalizer $normalizer,
    ) {
    }

    public function createNote(Model $actionable, string $title, ?string $body, int|string|null $actorId): void
    {
        $this->actionLogRelation($actionable)->create([
            'type' => ActionLogType::Note,
            'title' => $title,
            'body' => $body,
            'actor_id' => $this->normalizer->normalizeActorId($actorId),
        ]);
    }

    public function createSystemUpdate(Model $actionable, ActionLogSummary $summary): void
    {
        $this->actionLogRelation($actionable)->create([
            'type' => ActionLogType::System,
            'title' => $summary->title,
            'body' => $summary->body,
            'actor_id' => $this->normalizer->normalizeActorId(Auth::id()),
        ]);
    }

    public function delete(ActionLog $actionLog): void
    {
        $this->ensureManageable($actionLog);

        $actionLog->delete();
    }

    public function resolveForActionable(Model $actionable, int|string|null $actionLogId): ActionLog
    {
        $normalizedActionLogId = $this->normalizer->normalizeActionLogId($actionLogId);
        $actionLog = $this->actionLogRelation($actionable)
            ->get()
            ->first(static fn (ActionLog $actionLog): bool => $actionLog->id === $normalizedActionLogId)
        ;

        if (! $actionLog instanceof ActionLog) {
            throw new ModelNotFoundException()->setModel(ActionLog::class, [$normalizedActionLogId]);
        }

        return $actionLog;
    }

    public function update(ActionLog $actionLog, string $title, ?string $body): void
    {
        $this->ensureManageable($actionLog);

        $actionLog->update([
            'title' => $title,
            'body' => $body,
        ]);
    }

    /**
     * @return MorphMany<ActionLog, Model>
     */
    private function actionLogRelation(Model $actionable): MorphMany
    {
        if (! method_exists($actionable, 'actionLogs')) {
            throw new ModelNotFoundException();
        }

        $relation = $actionable->actionLogs();

        if (! $relation instanceof MorphMany) {
            throw new ModelNotFoundException();
        }

        return $relation;
    }

    private function ensureManageable(ActionLog $actionLog): void
    {
        if ($actionLog->type === ActionLogType::System) {
            throw new ModelNotFoundException()->setModel(ActionLog::class, [$this->normalizer->modelKey($actionLog)]);
        }
    }
}
