<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\CustomerType;
use App\Models\Customer;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
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
            ...self::getTimestampColumns(),
        ];
    }

    private static function makeTimestampColumn(string $name, bool $isVisibleByDefault = false): TextColumn
    {
        return TextColumn::make($name)
            ->label(Localization::translate("fields.{$name}"))
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: ! $isVisibleByDefault)
        ;
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
                ->sortable(),
            TextColumn::make('type')
                ->label(Localization::translate('fields.type'))
                ->badge()
                ->sortable(),
            TextColumn::make('country_code')
                ->label(Localization::translate('fields.country_code'))
                ->searchable()
                ->sortable()
                ->placeholder(Localization::translate('common.placeholder')),
            TextColumn::make('vat_id')
                ->label(Localization::translate('fields.vat_id'))
                ->searchable()
                ->placeholder(Localization::translate('common.placeholder')),
            TextColumn::make('email')
                ->label(Localization::translate('fields.email_address'))
                ->searchable()
                ->sortable()
                ->placeholder(Localization::translate('common.placeholder')),
        ];
    }

    /**
     * @return list<TextColumn>
     */
    private static function getTimestampColumns(): array
    {
        return [
            self::makeTimestampColumn('created_at', isVisibleByDefault: true),
            self::makeTimestampColumn('updated_at'),
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
            SelectFilter::make('type')
                ->label(Localization::translate('fields.type'))
                ->options(CustomerType::class),
            SelectFilter::make('country_code')
                ->label(Localization::translate('fields.country_code'))
                ->options(static fn (): array => Customer::all()
                    ->pluck('country_code', 'country_code')
                    ->filter(static fn (mixed $countryCode): bool => is_string($countryCode) && filled($countryCode))
                    ->sortKeys()
                    ->all()),
            TernaryFilter::make('is_vat_exempt')
                ->label(Localization::translate('fields.is_vat_exempt')),
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
