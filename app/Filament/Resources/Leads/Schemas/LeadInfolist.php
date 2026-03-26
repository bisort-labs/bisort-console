<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Lead;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getEntries());
    }

    /**
     * @return list<Section>
     */
    private static function getEntries(): array
    {
        return [
            self::getOverviewEntries(),
            self::getAddressEntries(),
            self::getTimestampEntries(),
        ];
    }

    private static function getOverviewEntries(): Section
    {
        return Section::make('Overview')
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('email')->label('Email address'),
                TextEntry::make('company')->placeholder('-'),
                TextEntry::make('phone')->placeholder('-'),
                TextEntry::make('source')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('owner.name')->label('Owner')->placeholder('-'),
            ])
        ;
    }

    private static function getAddressEntries(): Section
    {
        return Section::make('Address')
            ->schema([
                TextEntry::make('street')->placeholder('-'),
                TextEntry::make('city')->placeholder('-'),
                TextEntry::make('state')->placeholder('-'),
                TextEntry::make('zip')->placeholder('-'),
                TextEntry::make('country')->placeholder('-'),
            ])
        ;
    }

    private static function getTimestampEntries(): Section
    {
        return Section::make('History')
            ->columnSpanFull()
            ->schema([
                self::makeTimestampEntry('created_at'),
                self::makeTimestampEntry('updated_at'),
                self::getDeletedAtEntry(),
                self::getActionLogs(),
            ])
        ;
    }

    private static function makeTimestampEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->dateTime()
            ->placeholder('-')
        ;
    }

    private static function getDeletedAtEntry(): TextEntry
    {
        return TextEntry::make('deleted_at')
            ->dateTime()
            ->visible(fn (Lead $record): bool => $record->trashed())
        ;
    }

    private static function getActionLogs(): ViewEntry
    {
        return ViewEntry::make('timeline')
            ->label('Timeline')
            ->view('filament.resources.action_logs.partials.timeline')
            ->viewData(static fn (?Lead $record): array => [
                'actions' => $record?->getTimelineActions() ?? collect(),
            ])
        ;
    }
}
