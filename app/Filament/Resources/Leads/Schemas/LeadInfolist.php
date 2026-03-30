<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Mappers\ActionLogTimelineMapper;
use App\Models\Lead;
use App\Support\Localization;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::getOverviewEntries(),
            self::getAddressEntries(),
            self::getTimestampEntries(),
        ]);
    }

    private static function getOverviewEntries(): Section
    {
        return Section::make(Localization::translate('common.sections.overview'))
            ->schema([
                TextEntry::make('name')
                    ->label(Localization::translate('fields.name')),
                TextEntry::make('email')
                    ->label(Localization::translate('fields.email_address')),
                TextEntry::make('company')
                    ->label(Localization::translate('fields.company'))
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('phone')
                    ->label(Localization::translate('fields.phone'))
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('source')
                    ->label(Localization::translate('fields.source'))
                    ->placeholder(Localization::translate('common.placeholder'))
                    ->badge(),
                TextEntry::make('status')
                    ->label(Localization::translate('fields.status'))
                    ->placeholder(Localization::translate('common.placeholder'))
                    ->badge(),
                TextEntry::make('owner.name')
                    ->label(Localization::translate('fields.owner'))
                    ->placeholder(Localization::translate('common.placeholder')),
            ])
        ;
    }

    private static function getAddressEntries(): Section
    {
        return Section::make(Localization::translate('common.sections.address'))
            ->schema([
                TextEntry::make('street')
                    ->label(Localization::translate('fields.street'))
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('city')
                    ->label(Localization::translate('fields.city'))
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('state')
                    ->label(Localization::translate('fields.state'))
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('zip')
                    ->label(Localization::translate('fields.zip'))
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('country')
                    ->label(Localization::translate('fields.country'))
                    ->placeholder(Localization::translate('common.placeholder')),
            ])
        ;
    }

    private static function getTimestampEntries(): Section
    {
        return Section::make(Localization::translate('common.sections.history'))
            ->columnSpanFull()
            ->schema([
                TextEntry::make('created_at')
                    ->label(Localization::translate('fields.created_at'))
                    ->dateTime()
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('updated_at')
                    ->label(Localization::translate('fields.updated_at'))
                    ->dateTime()
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('deleted_at')
                    ->label(Localization::translate('fields.deleted_at'))
                    ->dateTime()
                    ->visible(fn (Lead $record): bool => $record->trashed()),
                ViewEntry::make('timeline')
                    ->label(Localization::translate('common.sections.timeline'))
                    ->view('filament.resources.action_logs.partials.timeline')
                    ->viewData(static fn (?Lead $record): array => [
                        'actions' => ActionLogTimelineMapper::map($record->actionLogs ?? new EloquentCollection()),
                    ]),
            ])
        ;
    }
}
