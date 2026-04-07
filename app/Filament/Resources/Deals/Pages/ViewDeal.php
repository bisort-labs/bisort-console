<?php

declare(strict_types=1);

namespace App\Filament\Resources\Deals\Pages;

use App\Filament\Resources\Deals\DealResource;
use App\Models\ActionLog;
use App\Models\Deal;
use App\Support\ActionLogs\DealActionLogManager;
use App\Support\Localization;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Override;

/**
 * @extends ViewRecord<Deal>
 */
class ViewDeal extends ViewRecord
{
    protected static string $resource = DealResource::class;

    public function editActionLogAction(): Action
    {
        return Action::make('editActionLog')
            ->label(Localization::translate('actions.edit_action_log'))
            ->modalHeading(Localization::translate('actions.edit_action_log'))
            ->modalSubmitActionLabel(Localization::translate('actions.save_changes'))
            ->record(fn (array $arguments): ActionLog => $this->actionLogManager()->resolveForDeal(
                $this->getRecord(),
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
                $this->refreshDealRecord();
                $this->sendActionLogUpdatedNotification();
            })
        ;
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
            ->record(fn (array $arguments): ActionLog => $this->actionLogManager()->resolveForDeal(
                $this->getRecord(),
                filter_var($arguments['actionLog'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            ))
            ->action(function (ActionLog $record): void {
                $this->actionLogManager()->delete($record);
                $this->refreshDealRecord();
                $this->sendActionLogDeletedNotification();
            })
        ;
    }

    /**
     * @return array<Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            $this->getAddNoteAction(),
        ];
    }

    private function getAddNoteAction(): Action
    {
        return Action::make('addNote')
            ->label(Localization::translate('actions.add_note'))
            ->icon('heroicon-o-pencil-square')
            ->modalHeading(Localization::translate('actions.add_note'))
            ->modalSubmitActionLabel(Localization::translate('actions.add_note'))
            ->schema($this->getActionLogSchema())
            ->action(function (array $data): void {
                $this->actionLogManager()->createNote(
                    $this->getRecord(),
                    $this->normalizeTitle($data['title'] ?? null),
                    $this->normalizeBody($data['body'] ?? null),
                    Auth::id(),
                );
                $this->refreshDealRecord();
                $this->sendNoteAddedNotification();
            })
        ;
    }

    /**
     * @return array<TextInput|Textarea>
     */
    private function getActionLogSchema(): array
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

    private function refreshDealRecord(): void
    {
        $this->record = $this->getRecord()->refresh();
    }

    private function actionLogManager(): DealActionLogManager
    {
        return app(DealActionLogManager::class);
    }

    private function sendActionLogUpdatedNotification(): void
    {
        Notification::make()
            ->title(Localization::translate('messages.notifications.action_log_updated'))
            ->success()
            ->send()
        ;
    }

    private function sendActionLogDeletedNotification(): void
    {
        Notification::make()
            ->title(Localization::translate('messages.notifications.action_log_deleted'))
            ->success()
            ->send()
        ;
    }

    private function sendNoteAddedNotification(): void
    {
        Notification::make()
            ->title(Localization::translate('messages.notifications.note_added'))
            ->success()
            ->send()
        ;
    }

    private function normalizeBody(mixed $body): ?string
    {
        return is_scalar($body) && filled($body) ? strval($body) : null;
    }

    private function normalizeTitle(mixed $title): string
    {
        return is_scalar($title) ? strval($title) : '';
    }
}
