<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Lead;
use App\Support\Localization;
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
        return Section::make(Localization::translate('common.sections.overview'))
            ->schema(self::getOverviewSchema())
        ;
    }

    /**
     * @return list<TextEntry>
     */
    private static function getOverviewSchema(): array
    {
        return [
            self::makeEntry('name', 'fields.name'),
            self::makeEntry('email', 'fields.email_address'),
            self::makePlaceholderEntry('company', 'fields.company'),
            self::makePlaceholderEntry('phone', 'fields.phone'),
            self::makeBadgePlaceholderEntry('source', 'fields.source'),
            self::makeBadgePlaceholderEntry('status', 'fields.status'),
            self::makePlaceholderEntry('owner.name', 'fields.owner'),
        ];
    }

    private static function getAddressEntries(): Section
    {
        return Section::make(Localization::translate('common.sections.address'))
            ->schema([
                self::makePlaceholderEntry('street', 'fields.street'),
                self::makePlaceholderEntry('city', 'fields.city'),
                self::makePlaceholderEntry('state', 'fields.state'),
                self::makePlaceholderEntry('zip', 'fields.zip'),
                self::makePlaceholderEntry('country', 'fields.country'),
            ])
        ;
    }

    private static function getTimestampEntries(): Section
    {
        return Section::make(Localization::translate('common.sections.history'))
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
            ->label(Localization::translate("fields.{$name}"))
            ->dateTime()
            ->placeholder(Localization::translate('common.placeholder'))
        ;
    }

    private static function makeEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label(Localization::translate($label))
        ;
    }

    private static function makePlaceholderEntry(string $name, string $label): TextEntry
    {
        return self::makeEntry($name, $label)
            ->placeholder(Localization::translate('common.placeholder'))
        ;
    }

    private static function makeBadgePlaceholderEntry(string $name, string $label): TextEntry
    {
        return self::makePlaceholderEntry($name, $label)
            ->badge()
        ;
    }

    private static function getDeletedAtEntry(): TextEntry
    {
        return TextEntry::make('deleted_at')
            ->label(Localization::translate('fields.deleted_at'))
            ->dateTime()
            ->visible(fn (Lead $record): bool => $record->trashed())
        ;
    }

    private static function getActionLogs(): ViewEntry
    {
        return ViewEntry::make('timeline')
            ->label(Localization::translate('common.sections.timeline'))
            ->view('filament.resources.action_logs.partials.timeline')
            ->viewData(static fn (?Lead $record): array => [
                'actions' => $record?->getTimelineActions() ?? collect(),
            ])
        ;
    }
}
