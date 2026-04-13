<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
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

class LeadsTable
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
            ...self::getContactColumns(),
            ...self::getTimestampColumns(),
        ];
    }

    /**
     * @return list<TextColumn>
     */
    private static function getOverviewColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(Localization::translate('fields.name'))
                ->searchable()
                ->placeholder(Localization::translate('common.placeholder')),
            TextColumn::make('company')->label(Localization::translate('fields.company'))->searchable()->sortable(),
            TextColumn::make('status')
                ->label(Localization::translate('fields.status'))
                ->badge()
                ->searchable()
                ->sortable(),
            TextColumn::make('source')->label(Localization::translate('fields.source'))->searchable()->sortable(),
        ];
    }

    /**
     * @return list<TextColumn>
     */
    private static function getContactColumns(): array
    {
        return [
            TextColumn::make('email')->label(Localization::translate('fields.email_address'))->searchable()->sortable(),
            TextColumn::make('phone')->label(Localization::translate('fields.phone'))->searchable(),
            TextColumn::make('owner.name')->label(Localization::translate('fields.owner'))->searchable()->sortable(),
        ];
    }

    /**
     * @return list<TextColumn>
     */
    private static function getTimestampColumns(): array
    {
        return [
            self::makeTimestampColumn('created_at'),
            self::makeTimestampColumn('updated_at'),
            self::makeTimestampColumn('deleted_at'),
        ];
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
     * @return list<BaseFilter>
     */
    private static function getFilters(): array
    {
        return [
            TrashedFilter::make(),
            SelectFilter::make('status')
                ->label(Localization::translate('fields.status'))
                ->options(LeadStatus::class)
                ->searchable(),
            SelectFilter::make('source')
                ->label(Localization::translate('fields.source'))
                ->options(LeadSource::class)
                ->searchable(),
            SelectFilter::make('owner')
                ->relationship('owner', 'name')
                ->searchable()
                ->label(Localization::translate('fields.owner')),
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
}
