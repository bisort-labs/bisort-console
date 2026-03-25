<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Lead;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getEntries());
    }

    /**
     * @return list<TextEntry>
     */
    private static function getEntries(): array
    {
        return [
            ...self::getOverviewEntries(),
            ...self::getAddressEntries(),
            ...self::getTimestampEntries(),
        ];
    }

    /**
     * @return list<TextEntry>
     */
    private static function getOverviewEntries(): array
    {
        return [
            TextEntry::make('name'),
            TextEntry::make('email')->label('Email address'),
            TextEntry::make('company')->placeholder('-'),
            TextEntry::make('phone')->placeholder('-'),
            TextEntry::make('source')->placeholder('-'),
            TextEntry::make('status')->badge()->placeholder('-'),
            TextEntry::make('owner.name')->label('Owner')->placeholder('-'),
        ];
    }

    /**
     * @return list<TextEntry>
     */
    private static function getAddressEntries(): array
    {
        return [
            TextEntry::make('street')->placeholder('-'),
            TextEntry::make('city')->placeholder('-'),
            TextEntry::make('state')->placeholder('-'),
            TextEntry::make('zip')->placeholder('-'),
            TextEntry::make('country')->placeholder('-'),
        ];
    }

    /**
     * @return list<TextEntry>
     */
    private static function getTimestampEntries(): array
    {
        return [
            self::makeTimestampEntry('created_at'),
            self::makeTimestampEntry('updated_at'),
            self::getDeletedAtEntry(),
        ];
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
}
