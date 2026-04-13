<?php

declare(strict_types=1);

namespace App\Filament\Resources\Deals\Tables;

use App\Enums\DealStage;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Deal;
use App\Services\Localization;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->recordActions(self::getRecordActions())
            ->toolbarActions(self::getToolbarActions())
        ;
    }

    /**
     * @return list<TextColumn>
     */
    private static function getColumns(): array
    {
        return [
            ...self::getOverviewColumns(),
            ...self::getStateColumns(),
            ...self::getTimestampColumns(),
        ];
    }

    /**
     * @return list<TextColumn>
     */
    private static function getOverviewColumns(): array
    {
        return [
            self::getTitleColumn(),
            self::getLeadColumn(),
            self::getValueColumn(),
        ];
    }

    /**
     * @return list<TextColumn>
     */
    private static function getStateColumns(): array
    {
        return [
            self::getStageColumn(),
            self::getCloseDateColumn(),
            self::getOwnerColumn(),
            self::getUpdatedAtColumn(),
        ];
    }

    /**
     * @return list<TextColumn>
     */
    private static function getTimestampColumns(): array
    {
        return [
            self::makeTimestampColumn('created_at'),
            self::makeTimestampColumn('deleted_at'),
        ];
    }

    /**
     * @return list<BaseFilter>
     */
    private static function getFilters(): array
    {
        return [
            TrashedFilter::make(),
            SelectFilter::make('stage')
                ->label(Localization::translate('fields.stage'))
                ->options(DealStage::class)
                ->searchable(),
            SelectFilter::make('owner')
                ->label(Localization::translate('fields.owner'))
                ->relationship('owner', 'name')
                ->searchable(),
            SelectFilter::make('lead')
                ->label(Localization::translate('fields.lead'))
                ->relationship('lead', 'name')
                ->searchable(),
        ];
    }

    /**
     * @return list<Action>
     */
    private static function getRecordActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
        ];
    }

    /**
     * @return list<ActionGroup>
     */
    private static function getToolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
                ForceDeleteBulkAction::make(),
                RestoreBulkAction::make(),
            ]),
        ];
    }

    private static function getTitleColumn(): TextColumn
    {
        return TextColumn::make('title')
            ->label(Localization::translate('fields.title'))
            ->searchable(query: self::applyGlobalSearch(...))
            ->sortable()
        ;
    }

    private static function getLeadColumn(): TextColumn
    {
        return TextColumn::make('lead.name')
            ->label(Localization::translate('fields.lead'))
            ->url(static fn (Deal $record): string => LeadResource::getUrl('view', ['record' => $record->lead]))
            ->searchable()
            ->sortable()
        ;
    }

    private static function getValueColumn(): TextColumn
    {
        return TextColumn::make('expected_value_cents')
            ->label(Localization::translate('fields.expected_value'))
            ->money(
                currency: static fn (Deal $record): string => $record->currency,
                divideBy: 100,
                decimalPlaces: 2,
            )
            ->sortable()
        ;
    }

    private static function getStageColumn(): TextColumn
    {
        return TextColumn::make('stage')
            ->label(Localization::translate('fields.stage'))
            ->badge()
            ->sortable()
        ;
    }

    private static function getCloseDateColumn(): TextColumn
    {
        return TextColumn::make('close_date')
            ->label(Localization::translate('fields.close_date'))
            ->date()
            ->sortable()
            ->placeholder(Localization::translate('common.placeholder'))
        ;
    }

    private static function getOwnerColumn(): TextColumn
    {
        return TextColumn::make('owner.name')
            ->label(Localization::translate('fields.owner'))
            ->sortable()
            ->placeholder(Localization::translate('common.placeholder'))
        ;
    }

    private static function getUpdatedAtColumn(): TextColumn
    {
        return TextColumn::make('updated_at')
            ->label(Localization::translate('fields.updated_at'))
            ->dateTime()
            ->sortable()
        ;
    }

    private static function makeTimestampColumn(string $name): TextColumn
    {
        return TextColumn::make($name)
            ->label(Localization::translate("fields.{$name}"))
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true)
        ;
    }

    /**
     * @param  Builder<Deal>  $query
     *
     * @return Builder<Deal>
     */
    private static function applyGlobalSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('title', 'like', "%{$search}%")
                ->orWhereHas('lead', function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%");
                })
            ;
        });
    }
}
