<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\ActionLog;
use App\Models\Lead;
use App\Support\ActionLogs\LeadActionLogManager;
use App\Support\Localization;
use App\Support\Notifications\ActionLogNotificationManager;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Override;

/**
 * @extends ViewRecord<Lead>
 */
class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    public function editActionLogAction(): Action
    {
        return Action::make('editActionLog')
            ->label(Localization::translate('actions.edit_action_log'))
            ->modalHeading(Localization::translate('actions.edit_action_log'))
            ->modalSubmitActionLabel(Localization::translate('actions.save_changes'))
            ->record(fn (array $arguments): ActionLog => app(LeadActionLogManager::class)
                ->resolveForLead(
                    $this->getRecord(),
                    filter_var($arguments['actionLog'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
                ))
            ->fillForm(fn (ActionLog $record): array => ['title' => $record->title, 'body' => $record->body])
            ->schema($this->getActionLogSchema())
            ->action(function (array $data, ActionLog $record): void {
                $title = $data['title'] ?? null;
                $body = $data['body'] ?? null;

                app(LeadActionLogManager::class)->update(
                    $record,
                    is_scalar($title) ? strval($title) : '',
                    is_scalar($body) && filled($body) ? strval($body) : null,
                );
                $this->refreshLeadRecord();
                app(ActionLogNotificationManager::class)->sendActionLogUpdated();
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
            ->record(fn (array $arguments): ActionLog => app(LeadActionLogManager::class)
                ->resolveForLead(
                    $this->getRecord(),
                    filter_var($arguments['actionLog'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
                ))
            ->action(function (ActionLog $record): void {
                app(LeadActionLogManager::class)->delete($record);
                $this->refreshLeadRecord();
                app(ActionLogNotificationManager::class)->sendActionLogDeleted();
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
                $title = $data['title'] ?? null;
                $body = $data['body'] ?? null;

                app(LeadActionLogManager::class)->createNote(
                    $this->getRecord(),
                    is_scalar($title) ? strval($title) : '',
                    is_scalar($body) && filled($body) ? strval($body) : null,
                    Auth::id(),
                );
                $this->refreshLeadRecord();
                app(ActionLogNotificationManager::class)->sendNoteAdded();
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

    private function refreshLeadRecord(): void
    {
        $this->record = $this->getRecord()->refresh();
    }
}
