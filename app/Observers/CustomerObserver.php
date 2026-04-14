<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ActionLogType;
use App\Models\Customer;
use App\Support\Localization;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        $this->createSystemAction(
            customer: $customer,
            title: Localization::translate('messages.customers.system_actions.created.title'),
            body: Localization::translate('messages.customers.system_actions.created.body'),
        );
    }

    public function updated(Customer $customer): void
    {
        $this->createSystemAction(
            customer: $customer,
            title: Localization::translate('messages.customers.system_actions.updated.title'),
            body: Localization::translate('messages.customers.system_actions.updated.body'),
        );
    }

    private function createSystemAction(Customer $customer, string $title, string $body): void
    {
        $customer->actionLogs()->create([
            'type' => ActionLogType::System,
            'title' => $title,
            'body' => $body,
            'actor_id' => null,
            'happened_at' => now(),
        ]);
    }
}
