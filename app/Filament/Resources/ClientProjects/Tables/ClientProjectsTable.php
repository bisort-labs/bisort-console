<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientProjects\Tables;

use App\Services\Localization;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ClientProjectsTable
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
     * @return list<Column>
     */
    private static function getColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label(Localization::translate('fields.id'))
                ->searchable()
                ->sortable(),
            TextColumn::make('name')
                ->label(Localization::translate('fields.name'))
                ->searchable(),
            TextColumn::make('slug')
                ->label(Localization::translate('fields.slug'))
                ->searchable()
                ->sortable(),
            IconColumn::make('is_active')
                ->label(Localization::translate('fields.active'))
                ->boolean(),
            ...self::getTimestampColumns(),
        ];
    }

    /**
     * @return list<Column>
     */
    private static function getTimestampColumns(): array
    {
        return [
            TextColumn::make('created_at')
                ->label(Localization::translate('fields.created_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->label(Localization::translate('fields.updated_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('deleted_at')
                ->label(Localization::translate('fields.deleted_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * @return list<BaseFilter>
     */
    private static function getFilters(): array
    {
        return [
            TrashedFilter::make(),
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
