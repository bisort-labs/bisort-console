<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getComponents());
    }

    /**
     * @return list<Component>
     */
    private static function getComponents(): array
    {
        return [
            ...self::getPrimaryFields(),
            ...self::getAddressFields(),
            ...self::getLeadFields(),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function getPrimaryFields(): array
    {
        return [
            TextInput::make('name')->required(),
            self::getEmailField(),
            TextInput::make('company'),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function getAddressFields(): array
    {
        return [
            TextInput::make('street'),
            TextInput::make('city'),
            TextInput::make('state'),
            TextInput::make('zip'),
            TextInput::make('country'),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function getLeadFields(): array
    {
        return [
            TextInput::make('phone')->tel(),
            Select::make('source')->options(LeadSource::class)->default(LeadSource::ColdOutreach),
            Select::make('status')->options(LeadStatus::class)->default(LeadStatus::New),
            Select::make('owner_id')->relationship('owner', 'name'),
        ];
    }

    private static function getEmailField(): TextInput
    {
        return TextInput::make('email')
            ->label('Email address')
            ->email()
            ->required()
            ->live(onBlur: true)
            ->unique(ignoreRecord: true)
            ->validationMessages([
                'unique' => 'Email address is already in use.',
            ])
        ;
    }
}
