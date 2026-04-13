<?php

declare(strict_types=1);

namespace App\Observers;

use App\DTOs\ActionLog\ActionLogSummary;
use App\Models\Lead;
use App\Support\ActionLogs\ActionLogChangeSummaryBuilder;
use App\Support\ActionLogs\ActionLogManager;

class LeadObserver
{
    public function updated(Lead $lead): void
    {
        $summary = $lead->pullPendingActionLogSummary();

        if (! $summary instanceof ActionLogSummary) {
            return;
        }

        app(ActionLogManager::class)->createSystemUpdate($lead, $summary);
    }

    public function updating(Lead $lead): void
    {
        $lead->rememberPendingActionLogSummary(
            app(ActionLogChangeSummaryBuilder::class)->build($lead),
        );
    }
}
