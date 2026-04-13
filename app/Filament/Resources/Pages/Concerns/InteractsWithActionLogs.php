<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Concerns;

use App\Enums\ActionLogType;
use App\Models\ActionLog;
use App\Services\ActionLog\ActionLogManager;
use App\Services\Localization;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @method Model getRecord()
 */
trait InteractsWithActionLogs
{
    public function editActionLogAction(): Action
    {
        return Action::make('editActionLog')
            ->label(Localization::translate('actions.edit_action_log'))
            ->modalHeading(Localization::translate('actions.edit_action_log'))
            ->modalSubmitActionLabel(Localization::translate('actions.save_changes'))
            ->record(fn (array $arguments): ActionLog => app(ActionLogManager::class)->resolveForActionable(
                $this->getRecord(),
                filter_var($arguments['actionLog'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            ))
            ->fillForm(static fn (ActionLog $record): array => ['title' => $record->title, 'body' => $record->body])
            ->schema($this->getActionLogSchema())
            ->action(function (array $data, ActionLog $record): void {
                if ($record->type === ActionLogType::System) {
                    $this->sendActionLogNotification('messages.notifications.action_log_not_modified', warning: true);
                    return;
                }
                app(ActionLogManager::class)->update(
                    $record,
                    is_scalar($data['title'] ?? null) ? strval($data['title']) : '',
                    is_scalar($data['body'] ?? null) && filled($data['body']) ? strval($data['body']) : null,
                );
                $this->record = $this->getRecord()->refresh();
                $this->sendActionLogNotification('messages.notifications.action_log_updated');
            });
    }

    public function deleteActionLogAction(): Action
    {
        return Action::make('deleteActionLog')
            ->label(Localization::translate('actions.delete_action_log'))
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(Localization::translate('actions.delete_action_log'))
            ->modalDescription(Localization::translate('messages.timeline.delete_action_log_confirmation'))
            ->modalSubmitActionLabel(Localization::translate('actions.delete_action_log'))
            ->record(fn (array $arguments): ActionLog => app(ActionLogManager::class)->resolveForActionable(
                $this->getRecord(),
                filter_var($arguments['actionLog'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            ))
            ->action(function (ActionLog $record): void {
                if ($record->type === ActionLogType::System) {
                    $this->sendActionLogNotification('messages.notifications.action_log_not_modified', warning: true);
                    return;
                }
                app(ActionLogManager::class)->delete($record);
                $this->record = $this->getRecord()->refresh();
                $this->sendActionLogNotification('messages.notifications.action_log_deleted');
            });
    }

    /**
     * @return array<TextInput|Textarea>
     */
    protected function getActionLogSchema(): array
    {
        return [
            TextInput::make('title')
                ->label(Localization::translate('fields.title'))
                ->required()
                ->maxLength(255),
            Textarea::make('body')
                ->label(Localization::translate('fields.body'))
                ->rows(6),
        ];
    }

    protected function getAddNoteAction(): Action
    {
        return Action::make('addNote')
            ->label(Localization::translate('actions.add_note'))
            ->icon('heroicon-o-pencil-square')
            ->modalHeading(Localization::translate('actions.add_note'))
            ->modalSubmitActionLabel(Localization::translate('actions.add_note'))
            ->schema($this->getActionLogSchema())
            ->action(function (array $data): void {
                app(ActionLogManager::class)->createNote(
                    $this->getRecord(),
                    is_scalar($data['title'] ?? null) ? strval($data['title']) : '',
                    is_scalar($data['body'] ?? null) && filled($data['body']) ? strval($data['body']) : null,
                    Auth::id(),
                );
                $this->record = $this->getRecord()->refresh();
                $this->sendActionLogNotification('messages.notifications.note_added');
            });
    }

    protected function sendActionLogNotification(string $translationKey, bool $warning = false): void
    {
        $notification = Notification::make()->title(Localization::translate($translationKey));

        if ($warning) {
            $notification->warning()->send();

            return;
        }

        $notification->success()->send();
    }
}
