<?php

declare(strict_types=1);

namespace App\Support\Notifications;

use App\Support\Localization;
use Filament\Notifications\Notification;

class ActionLogNotificationManager
{
    public function sendActionLogUpdated(): void
    {
        $this->sendSuccessNotification('messages.notifications.action_log_updated');
    }

    public function sendActionLogDeleted(): void
    {
        $this->sendSuccessNotification('messages.notifications.action_log_deleted');
    }

    public function sendNoteAdded(): void
    {
        $this->sendSuccessNotification('messages.notifications.note_added');
    }

    private function sendSuccessNotification(string $translationKey): void
    {
        Notification::make()
            ->title(Localization::translate($translationKey))
            ->success()
            ->send()
        ;
    }
}
