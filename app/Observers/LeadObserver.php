<?php

declare(strict_types=1);

namespace App\Observers;

use App\DTOs\ActionLog\ActionLogSummary;
use App\Models\Lead;
use App\Services\ActionLog\ActionLogChangeSummaryBuilder;
use App\Services\ActionLog\ActionLogManager;

class LeadObserver
{
    public function updating(Lead $lead): void
    {
        $lead->rememberPendingActionLogSummary(
            app(ActionLogChangeSummaryBuilder::class)->build($lead),
        );
    }

    public function updated(Lead $lead): void
    {
        $summary = $lead->pullPendingActionLogSummary();

        if (! $summary instanceof ActionLogSummary) {
            return;
        }

        app(ActionLogManager::class)->createSystemUpdate($lead, $summary);
    }
}
