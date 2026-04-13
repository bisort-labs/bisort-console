<?php

declare(strict_types=1);

namespace App\Filament\Resources\Deals\Schemas;

use App\Enums\DealStage;
use App\Filament\Resources\Leads\LeadResource;
use App\Mappers\ActionLogTimelineMapper;
use App\Models\Deal;
use App\Services\Localization;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class DealInfolist
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
            ->columnSpanFull()
            ->schema([
                self::getTitleEntry(),
                self::getLeadEntry(),
                self::getStageEntry(),
                self::getExpectedValueEntry(),
                self::getCurrencyEntry(),
                self::getProbabilityEntry(),
                self::getCloseDateEntry(),
                self::getOwnerEntry(),
                self::getProjectEntry(),
                self::getLostReasonEntry(),
                self::getNotesEntry(),
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
                    ->visible(static fn (Deal $record): bool => $record->trashed()),
                ViewEntry::make('timeline')
                    ->label(Localization::translate('common.sections.timeline'))
                    ->view('filament.resources.action_logs.partials.timeline')
                    ->viewData(static fn (?Deal $record): array => [
                        'actions' => ActionLogTimelineMapper::map($record->actionLogs ?? new EloquentCollection()),
                    ]),
            ])
        ;
    }

    private static function getTitleEntry(): TextEntry
    {
        return TextEntry::make('title')
            ->label(Localization::translate('fields.title'))
        ;
    }

    private static function getLeadEntry(): TextEntry
    {
        return TextEntry::make('lead.name')
            ->label(Localization::translate('fields.lead'))
            ->url(static fn (Deal $record): string => LeadResource::getUrl('view', ['record' => $record->lead]))
        ;
    }

    private static function getStageEntry(): TextEntry
    {
        return TextEntry::make('stage')
            ->label(Localization::translate('fields.stage'))
            ->badge()
        ;
    }

    private static function getExpectedValueEntry(): TextEntry
    {
        return TextEntry::make('expected_value_cents')
            ->label(Localization::translate('fields.expected_value'))
            ->money(
                currency: static fn (Deal $record): string => $record->currency,
                divideBy: 100,
                decimalPlaces: 2,
            )
        ;
    }

    private static function getCurrencyEntry(): TextEntry
    {
        return TextEntry::make('currency')
            ->label(Localization::translate('fields.currency'))
        ;
    }

    private static function getProbabilityEntry(): TextEntry
    {
        return TextEntry::make('probability')
            ->label(Localization::translate('fields.probability'))
            ->suffix('%')
            ->placeholder(Localization::translate('common.placeholder'))
        ;
    }

    private static function getCloseDateEntry(): TextEntry
    {
        return TextEntry::make('close_date')
            ->label(Localization::translate('fields.close_date'))
            ->date()
            ->placeholder(Localization::translate('common.placeholder'))
        ;
    }

    private static function getOwnerEntry(): TextEntry
    {
        return TextEntry::make('owner.name')
            ->label(Localization::translate('fields.owner'))
            ->placeholder(Localization::translate('common.placeholder'))
        ;
    }

    private static function getProjectEntry(): TextEntry
    {
        return TextEntry::make('project.name')
            ->label(Localization::translate('fields.project'))
            ->placeholder(Localization::translate('common.placeholder'))
            ->visible(
                static fn (Deal $record): bool => $record->stage === DealStage::Won || filled($record->project_id)
            )
        ;
    }

    private static function getLostReasonEntry(): TextEntry
    {
        return TextEntry::make('lost_reason')
            ->label(Localization::translate('fields.lost_reason'))
            ->placeholder(Localization::translate('common.placeholder'))
            ->visible(
                static fn (Deal $record): bool => $record->stage === DealStage::Lost || filled($record->lost_reason)
            )
        ;
    }

    private static function getNotesEntry(): TextEntry
    {
        return TextEntry::make('notes')
            ->label(Localization::translate('fields.notes'))
            ->placeholder(Localization::translate('common.placeholder'))
            ->columnSpanFull()
        ;
    }
}
