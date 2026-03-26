<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Pages;

use App\Enums\ActionLogType;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
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
 * @extends ViewRecord<Lead>
 */
class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

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
            ->schema($this->getAddNoteSchema())
            ->action(function (array $data): void {
                $title = isset($data['title']) && is_string($data['title']) ? $data['title'] : null;
                $body = isset($data['body']) && is_string($data['body']) ? $data['body'] : null;

                $this->addNote($title, $body);
            })
        ;
    }

    /**
     * @return array<TextInput|Textarea>
     */
    private function getAddNoteSchema(): array
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

    private function addNote(?string $title, ?string $body): void
    {
        $this->getRecord()->actionLogs()->create([
            'type' => ActionLogType::Note,
            'title' => filled($title) ? $title : null,
            'body' => filled($body) ? $body : null,
            'actor_id' => Auth::id(),
        ]);

        Notification::make()
            ->title(Localization::translate('messages.notifications.note_added'))
            ->success()
            ->send()
        ;
    }
}
