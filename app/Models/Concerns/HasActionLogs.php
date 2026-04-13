<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\DTOs\ActionLog\ActionLogSummary;
use App\Models\ActionLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActionLogs
{
    private ?ActionLogSummary $pendingActionLogSummary = null;

    /**
     * @return MorphMany<ActionLog, $this>
     */
    public function actionLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'actionable');
    }

    public function pullPendingActionLogSummary(): ?ActionLogSummary
    {
        $summary = $this->pendingActionLogSummary;

        $this->pendingActionLogSummary = null;

        return $summary;
    }

    public function rememberPendingActionLogSummary(?ActionLogSummary $summary): void
    {
        $this->pendingActionLogSummary = $summary;
    }
}
