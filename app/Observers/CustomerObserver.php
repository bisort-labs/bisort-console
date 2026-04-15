<?php

declare(strict_types=1);

namespace App\Observers;

use App\DTOs\ActionLog\ActionLogSummary;
use App\Enums\ActionLogType;
use App\Models\Customer;
use App\Services\ActionLog\ActionLogChangeSummaryBuilder;
use App\Services\ActionLog\ActionLogManager;
use App\Services\Localization;
use Illuminate\Support\Facades\Auth;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        $customer->actionLogs()->create([
            'type' => ActionLogType::System,
            'title' => Localization::translate('messages.timeline.customer_created'),
            'body' => Localization::translate('messages.timeline.customer_created_body'),
            'actor_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Customer "updating" event.
     */
    public function updating(Customer $customer): void
    {
        $customer->rememberPendingActionLogSummary(
            app(ActionLogChangeSummaryBuilder::class)->build($customer),
        );
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        $summary = $customer->pullPendingActionLogSummary();

        if (! $summary instanceof ActionLogSummary) {
            return;
        }

        app(ActionLogManager::class)->createSystemUpdate($customer, $summary);
    }
}
