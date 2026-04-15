<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerType;
use App\Services\Localization;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                self::getCountryCodeField(),
                self::getVatIdField(),
                self::getTaxNumberField(),
                self::getVatExemptField(),
                self::getVatExemptionReasonField(),
            ])
        ;
    }

    private static function getBillingAddressFields(): Section
    {
        return Section::make(Localization::translate('common.sections.billing_address'))
            ->columnSpanFull()
            ->schema([
                TextInput::make('billing_address.street')
                    ->label(Localization::translate('fields.street'))
                    ->maxLength(255),
                TextInput::make('billing_address.city')
                    ->label(Localization::translate('fields.city'))
                    ->maxLength(255),
                TextInput::make('billing_address.state')
                    ->label(Localization::translate('fields.state'))
                    ->maxLength(255),
                TextInput::make('billing_address.zip')
                    ->label(Localization::translate('fields.zip'))
                    ->maxLength(255),
                TextInput::make('billing_address.country')
                    ->label(Localization::translate('fields.country'))
                    ->maxLength(255),
            ])
        ;
    }

    private static function isVatExemptEnabled(mixed $state): bool
    {
        return filter_var($state, FILTER_VALIDATE_BOOL);
    }

    private static function getCountryCodeField(): TextInput
    {
        return TextInput::make('country_code')
            ->label(Localization::translate('fields.country_code'))
            ->minLength(2)
            ->maxLength(2)
            ->dehydrateStateUsing(
                static fn (mixed $state): ?string => is_scalar($state) && filled($state)
                    ? strtoupper(strval($state))
                    : null,
            )
        ;
    }

    private static function getVatIdField(): TextInput
    {
        return TextInput::make('vat_id')
            ->label(Localization::translate('fields.vat_id'))
            ->maxLength(255)
        ;
    }

    private static function getTaxNumberField(): TextInput
    {
        return TextInput::make('tax_number')
            ->label(Localization::translate('fields.tax_number'))
            ->maxLength(255)
        ;
    }

    private static function getVatExemptField(): Toggle
    {
        return Toggle::make('is_vat_exempt')
            ->label(Localization::translate('fields.is_vat_exempt'))
            ->default(false)
            ->live()
            ->afterStateUpdated(static function (mixed $state, Set $set): void {
                if (! self::isVatExemptEnabled($state)) {
                    $set('vat_exemption_reason', null);
                }
            })
        ;
    }

    private static function getVatExemptionReasonField(): Textarea
    {
        return Textarea::make('vat_exemption_reason')
            ->label(Localization::translate('fields.vat_exemption_reason'))
            ->rows(4)
            ->visible(static fn (Get $get): bool => self::isVatExemptEnabled($get('is_vat_exempt')))
            ->required(static fn (Get $get): bool => self::isVatExemptEnabled($get('is_vat_exempt')))
            ->dehydratedWhenHidden()
            ->columnSpanFull()
            ->validationMessages([
                'required' => Localization::translate('messages.validation.vat_exemption_reason_required'),
            ])
        ;
    }
}
