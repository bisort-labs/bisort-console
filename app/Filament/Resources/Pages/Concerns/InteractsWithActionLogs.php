<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Concerns;

use App\Models\ActionLog;
use App\Support\ActionLogs\ActionLogManager;
use App\Support\Localization;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * @method Model getRecord()
 */
trait InteractsWithActionLogs
{
    public function deleteActionLogAction(): Action
    {
        return Action::make('deleteActionLog')
            ->label(Localization::translate('actions.delete_action_log'))
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(Localization::translate('actions.delete_action_log'))
            ->modalDescription(Localization::translate('messages.timeline.delete_action_log_confirmation'))
            ->modalSubmitActionLabel(Localization::translate('actions.delete_action_log'))
            ->record(fn (array $arguments): ActionLog => $this->actionLogManager()->resolveForActionable(
                $this->getActionableRecord(),
                filter_var($arguments['actionLog'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            ))
            ->action(function (ActionLog $record): void {
                $this->actionLogManager()->delete($record);
                $this->refreshActionableRecord();
                $this->sendActionLogDeletedNotification();
            })
        ;
    }

    public function editActionLogAction(): Action
    {
        return Action::make('editActionLog')
            ->label(Localization::translate('actions.edit_action_log'))
            ->modalHeading(Localization::translate('actions.edit_action_log'))
            ->modalSubmitActionLabel(Localization::translate('actions.save_changes'))
            ->record(fn (array $arguments): ActionLog => $this->actionLogManager()->resolveForActionable(
                $this->getActionableRecord(),
                filter_var($arguments['actionLog'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            ))
            ->fillForm(static fn (ActionLog $record): array => ['title' => $record->title, 'body' => $record->body])
            ->schema($this->getActionLogSchema())
            ->action(function (array $data, ActionLog $record): void {
                $this->actionLogManager()->update(
                    $record,
                    $this->normalizeTitle($data['title'] ?? null),
                    $this->normalizeBody($data['body'] ?? null),
                );
                $this->refreshActionableRecord();
                $this->sendActionLogUpdatedNotification();
            })
        ;
    }

    protected function actionLogManager(): ActionLogManager
    {
        return app(ActionLogManager::class);
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
                $this->actionLogManager()->createNote(
                    $this->getActionableRecord(),
                    $this->normalizeTitle($data['title'] ?? null),
                    $this->normalizeBody($data['body'] ?? null),
                    Auth::id(),
                );
                $this->refreshActionableRecord();
                $this->sendNoteAddedNotification();
            })
        ;
    }

    protected function getActionableRecord(): Model
    {
        $record = $this->getRecord();

        if (! method_exists($record, 'actionLogs')) {
            throw new RuntimeException('The current record does not support action logs.');
        }

        return $record;
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

    protected function normalizeBody(mixed $body): ?string
    {
        return is_scalar($body) && filled($body) ? strval($body) : null;
    }

    protected function normalizeTitle(mixed $title): string
    {
        return is_scalar($title) ? strval($title) : '';
    }

    protected function refreshActionableRecord(): void
    {
        $this->record = $this->getActionableRecord()->refresh();
    }

    protected function sendActionLogDeletedNotification(): void
    {
        Notification::make()
            ->title(Localization::translate('messages.notifications.action_log_deleted'))
            ->success()
            ->send()
        ;
    }

    protected function sendActionLogUpdatedNotification(): void
    {
        Notification::make()
            ->title(Localization::translate('messages.notifications.action_log_updated'))
            ->success()
            ->send()
        ;
    }

    protected function sendNoteAddedNotification(): void
    {
        Notification::make()
            ->title(Localization::translate('messages.notifications.note_added'))
            ->success()
            ->send()
        ;
    }
}
