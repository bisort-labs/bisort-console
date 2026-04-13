<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Services\Localization;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::getPrimaryFields(),
            self::getLeadFields(),
            self::getAddressFields(),
        ]);
    }

    private static function getPrimaryFields(): Section
    {
        return Section::make(Localization::translate('common.sections.overview'))
            ->schema([
                TextInput::make('name')
                    ->label(Localization::translate('fields.name'))
                    ->required(),
                TextInput::make('email')
                    ->label(Localization::translate('fields.email_address'))
                    ->email()
                    ->required()
                    ->live(onBlur: true)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => Localization::translate('messages.validation.email_address_in_use'),
                    ]),
                TextInput::make('company')->label(Localization::translate('fields.company')),
            ])
        ;
    }

    private static function getAddressFields(): Section
    {
        return Section::make(Localization::translate('common.sections.address'))
            ->columnSpanFull()
            ->schema([
                TextInput::make('street')->label(Localization::translate('fields.street')),
                TextInput::make('city')->label(Localization::translate('fields.city')),
                TextInput::make('state')->label(Localization::translate('fields.state')),
                TextInput::make('zip')->label(Localization::translate('fields.zip')),
                TextInput::make('country')->label(Localization::translate('fields.country')),
            ])
        ;
    }

    private static function getLeadFields(): Section
    {
        return Section::make(Localization::translate('common.sections.lead_details'))
            ->schema([
                TextInput::make('phone')->label(Localization::translate('fields.phone'))->tel(),
                Select::make('source')
                    ->label(Localization::translate('fields.source'))
                    ->options(LeadSource::class)
                    ->default(LeadSource::ColdOutreach),
                Select::make('status')
                    ->label(Localization::translate('fields.status'))
                    ->options(LeadStatus::class)
                    ->default(LeadStatus::New),
                Select::make('owner_id')->label(Localization::translate('fields.owner'))->relationship('owner', 'name'),
            ])
        ;
    }
}
