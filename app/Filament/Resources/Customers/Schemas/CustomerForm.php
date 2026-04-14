<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerType;
use App\Support\Localization;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::getIdentityFields(),
            self::getContactFields(),
            self::getTaxFields(),
            self::getBillingAddressFields(),
            self::getDefaultFields(),
        ]);
    }

    private static function getIdentityFields(): Section
    {
        return Section::make(Localization::translate('common.sections.identity'))
            ->schema([
                TextInput::make('name')
                    ->label(Localization::translate('fields.name'))
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label(Localization::translate('fields.type'))
                    ->options(CustomerType::class)
                    ->default(CustomerType::B2B)
                    ->required(),
            ])
        ;
    }

    private static function getContactFields(): Section
    {
        return Section::make(Localization::translate('common.sections.contact'))
            ->schema([
                TextInput::make('email')
                    ->label(Localization::translate('fields.email_address'))
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label(Localization::translate('fields.phone'))
                    ->tel()
                    ->maxLength(255),
            ])
        ;
    }

    private static function getTaxFields(): Section
    {
        return Section::make(Localization::translate('common.sections.tax'))
            ->schema([
                TextInput::make('country_code')
                    ->label(Localization::translate('fields.country_code'))
                    ->minLength(2)
                    ->maxLength(2)
                    ->dehydrateStateUsing(static fn (?string $state): ?string => filled($state) ? strtoupper($state) : null),
                TextInput::make('vat_id')
                    ->label(Localization::translate('fields.vat_id'))
                    ->maxLength(255),
                TextInput::make('tax_number')
                    ->label(Localization::translate('fields.tax_number'))
                    ->maxLength(255),
                Toggle::make('is_vat_exempt')
                    ->label(Localization::translate('fields.is_vat_exempt'))
                    ->default(false)
                    ->live(),
                Textarea::make('vat_exemption_reason')
                    ->label(Localization::translate('fields.vat_exemption_reason'))
                    ->visible(static fn (Get $get): bool => (bool) $get('is_vat_exempt'))
                    ->required(static fn (Get $get): bool => (bool) $get('is_vat_exempt'))
                    ->columnSpanFull(),
            ])
        ;
    }

    private static function getBillingAddressFields(): Section
    {
        return Section::make(Localization::translate('common.sections.billing_address'))
            ->columnSpanFull()
            ->schema([
                TextInput::make('billing_address.street')
                    ->label(Localization::translate('fields.street')),
                TextInput::make('billing_address.city')
                    ->label(Localization::translate('fields.city')),
                TextInput::make('billing_address.state')
                    ->label(Localization::translate('fields.state')),
                TextInput::make('billing_address.zip')
                    ->label(Localization::translate('fields.zip')),
                TextInput::make('billing_address.country')
                    ->label(Localization::translate('fields.country')),
            ])
        ;
    }

    private static function getDefaultFields(): Section
    {
        return Section::make(Localization::translate('common.sections.defaults'))
            ->schema([
                TextInput::make('payment_terms_days')
                    ->label(Localization::translate('fields.payment_terms_days'))
                    ->numeric()
                    ->minValue(0),
            ])
        ;
    }
}
