<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientProjects\Schemas;

use App\Models\ClientProject;
use App\Support\Localization;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::getOverviewEntries(),
            self::getTimestampEntries(),
        ]);
    }

    private static function getOverviewEntries(): Section
    {
        return Section::make(Localization::translate('common.sections.overview'))
            ->schema([
                TextEntry::make('name')->label(Localization::translate('fields.name')),
                TextEntry::make('slug')->label(Localization::translate('fields.slug')),
                TextEntry::make('description')
                    ->label(Localization::translate('fields.description'))
                    ->placeholder(Localization::translate('common.placeholder'))
                    ->columnSpanFull(),
                IconEntry::make('is_active')
                    ->label(Localization::translate('fields.active'))
                    ->boolean(),
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
                    ->visible(fn (ClientProject $record): bool => $record->trashed()),
            ])
        ;
    }
}
