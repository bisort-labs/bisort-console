<?php

declare(strict_types=1);

namespace App\Observers;

use App\DTOs\ActionLog\ActionLogSummary;
use App\Models\Deal;
use App\Support\ActionLogs\ActionLogChangeSummaryBuilder;
use App\Support\ActionLogs\ActionLogManager;

class DealObserver
{
    public function updated(Deal $deal): void
    {
        $summary = $deal->pullPendingActionLogSummary();

        if (! $summary instanceof ActionLogSummary) {
            return;
        }

        app(ActionLogManager::class)->createSystemUpdate($deal, $summary);
    }

    public function updating(Deal $deal): void
    {
        $deal->rememberPendingActionLogSummary(
            app(ActionLogChangeSummaryBuilder::class)->build($deal),
        );
    }
}
