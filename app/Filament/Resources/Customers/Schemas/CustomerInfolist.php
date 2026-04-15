<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers\Schemas;

use App\Mappers\ActionLogTimelineMapper;
use App\Models\Customer;
use App\Services\BillingAddressFormatter;
use App\Services\Localization;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::getOverviewEntries(),
            self::getTaxEntries(),
            self::getBillingAddressEntries(),
            self::getTimestampEntries(),
        ]);
    }

    private static function getOverviewEntries(): Section
    {
        return Section::make(Localization::translate('common.sections.overview'))
            ->schema([
                TextEntry::make('name')
                    ->label(Localization::translate('fields.name')),
                TextEntry::make('type')
                    ->label(Localization::translate('fields.type'))
                    ->badge(),
                TextEntry::make('email')
                    ->label(Localization::translate('fields.email_address'))
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('phone')
                    ->label(Localization::translate('fields.phone'))
                    ->placeholder(Localization::translate('common.placeholder')),
            ])
        ;
    }

    private static function getTaxEntries(): Section
    {
        return Section::make(Localization::translate('common.sections.tax'))
            ->schema([
                TextEntry::make('country_code')
                    ->label(Localization::translate('fields.country_code'))
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('vat_id')
                    ->label(Localization::translate('fields.vat_id'))
                    ->placeholder(Localization::translate('common.placeholder')),
                TextEntry::make('tax_number')
                    ->label(Localization::translate('fields.tax_number'))
                    ->placeholder(Localization::translate('common.placeholder')),
                IconEntry::make('is_vat_exempt')
                    ->label(Localization::translate('fields.is_vat_exempt'))
                    ->boolean(),
                TextEntry::make('vat_exemption_reason')
                    ->label(Localization::translate('fields.vat_exemption_reason'))
                    ->placeholder(Localization::translate('common.placeholder'))
                    ->columnSpanFull(),
            ])
        ;
    }

    private static function getBillingAddressEntries(): Section
    {
        return Section::make(Localization::translate('common.sections.billing_address'))
            ->columnSpanFull()
            ->schema([
                TextEntry::make('billing_address_preview')
                    ->label(Localization::translate('fields.billing_address'))
                    ->state(
                        static fn (Customer $record): string => app(BillingAddressFormatter::class)
                            ->format($record->billing_address),
                    )
                    ->placeholder(Localization::translate('common.placeholder'))
                    ->columnSpanFull(),
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
                    ->visible(static fn (Customer $record): bool => $record->trashed()),
                ViewEntry::make('timeline')
                    ->label(Localization::translate('common.sections.timeline'))
                    ->view('filament.resources.action_logs.partials.timeline')
                    ->viewData(static fn (?Customer $record): array => [
                        'actions' => ActionLogTimelineMapper::map($record->actionLogs ?? new EloquentCollection()),
                    ]),
            ])
        ;
    }
}
