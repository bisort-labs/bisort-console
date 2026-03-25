<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
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
            TextColumn::make('name')->searchable()->placeholder('-'),
            TextColumn::make('company')->searchable()->sortable(),
            TextColumn::make('status')->badge()->searchable()->sortable(),
            TextColumn::make('source')->searchable()->sortable(),
        ];
    }

    /**
     * @return list<TextColumn>
     */
    private static function getContactColumns(): array
    {
        return [
            TextColumn::make('email')->label('Email address')->searchable()->sortable(),
            TextColumn::make('phone')->searchable(),
            TextColumn::make('owner.name')->searchable()->sortable(),
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
            SelectFilter::make('status')->options(LeadStatus::class)->searchable(),
            SelectFilter::make('source')->options(LeadSource::class)->searchable(),
            SelectFilter::make('owner')->relationship('owner', 'name')->searchable()->label('Owner'),
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
