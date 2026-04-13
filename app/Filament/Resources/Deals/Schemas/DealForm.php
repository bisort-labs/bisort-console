<?php

declare(strict_types=1);

namespace App\Filament\Resources\Deals\Schemas;

use App\Enums\DealStage;
use App\Models\Lead;
use App\Services\Deal\DealMoney;
use App\Services\Localization;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class DealForm
{
    private const string EXPECTED_VALUE_REG_EX = '/^\d+(?:[.,]\d{1,2})?$/';

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::getOverviewFields(),
            self::getDetailFields(),
        ]);
    }

    private static function getOverviewFields(): Section
    {
        return Section::make(Localization::translate('common.sections.overview'))
            ->columnSpanFull()
            ->schema([
                self::getLeadField(),
                self::getTitleField(),
                self::getStageField(),
                self::getExpectedValueField(),
            ])
        ;
    }

    private static function getDetailFields(): Section
    {
        return Section::make(Localization::translate('common.sections.deal_details'))
            ->columnSpanFull()
            ->schema([
                self::getCurrencyField(),
                self::getProbabilityField(),
                self::getCloseDateField(),
                self::getOwnerField(),
                self::getProjectField(),
                self::getLostReasonField(),
                self::getNotesField(),
            ])
        ;
    }

    private static function getLeadField(): Select
    {
        return Select::make('lead_id')
            ->label(Localization::translate('fields.lead'))
            ->relationship('lead', 'name')
            ->getOptionLabelFromRecordUsing(
                static fn (Lead $record): string => filled($record->company)
                    ? sprintf('%s (%s)', $record->name, $record->company)
                    : $record->name
            )
            ->searchable(['name', 'company'])
            ->preload()
            ->required()
        ;
    }

    private static function getTitleField(): TextInput
    {
        return TextInput::make('title')
            ->label(Localization::translate('fields.title'))
            ->required()
            ->maxLength(255)
        ;
    }

    private static function getStageField(): Select
    {
        return Select::make('stage')
            ->label(Localization::translate('fields.stage'))
            ->options(DealStage::class)
            ->default(DealStage::New)
            ->live()
            ->afterStateUpdated(static function (mixed $state, Set $set): void {
                self::clearConditionalFields($state, $set);
            })
            ->required()
        ;
    }

    private static function getExpectedValueField(): TextInput
    {
        return TextInput::make('expected_value_cents')
            ->label(Localization::translate('fields.expected_value'))
            ->required()
            ->inputMode('decimal')
            ->default('0.00')
            ->formatStateUsing(static fn (mixed $state): string => DealMoney::centsToAmount(
                is_numeric($state) ? (int) $state : null,
            ))
            ->dehydrateStateUsing(static fn (mixed $state): int => DealMoney::amountToCents(
                is_scalar($state) ? strval($state) : null,
            ))
            ->rule('regex:' . self::EXPECTED_VALUE_REG_EX)
        ;
    }

    private static function getCurrencyField(): TextInput
    {
        return TextInput::make('currency')
            ->label(Localization::translate('fields.currency'))
            ->required()
            ->default('EUR')
            ->minLength(3)
            ->maxLength(3)
            ->formatStateUsing(static fn (mixed $state): string => self::formatCurrency($state))
            ->dehydrateStateUsing(static fn (mixed $state): string => self::formatCurrency($state))
        ;
    }

    private static function getProbabilityField(): TextInput
    {
        return TextInput::make('probability')
            ->label(Localization::translate('fields.probability'))
            ->numeric()
            ->minValue(0)
            ->maxValue(100)
        ;
    }

    private static function getCloseDateField(): DatePicker
    {
        return DatePicker::make('close_date')
            ->label(Localization::translate('fields.close_date'))
        ;
    }

    private static function getOwnerField(): Select
    {
        return Select::make('owner_id')
            ->label(Localization::translate('fields.owner'))
            ->relationship('owner', 'name')
            ->searchable()
            ->preload()
        ;
    }

    private static function getProjectField(): Select
    {
        return Select::make('project_id')
            ->label(Localization::translate('fields.project'))
            ->relationship('project', 'name')
            ->searchable()
            ->preload()
            ->visible(static fn (Get $get): bool => self::stageMatches($get('stage'), DealStage::Won))
            ->dehydratedWhenHidden()
        ;
    }

    private static function getLostReasonField(): TextInput
    {
        return TextInput::make('lost_reason')
            ->label(Localization::translate('fields.lost_reason'))
            ->maxLength(255)
            ->visible(static fn (Get $get): bool => self::stageMatches($get('stage'), DealStage::Lost))
            ->dehydratedWhenHidden()
            ->required(static fn (Get $get): bool => self::stageMatches($get('stage'), DealStage::Lost))
            ->validationMessages([
                'required' => Localization::translate('messages.validation.lost_reason_required'),
            ])
        ;
    }

    private static function getNotesField(): Textarea
    {
        return Textarea::make('notes')
            ->label(Localization::translate('fields.notes'))
            ->rows(6)
            ->columnSpanFull()
        ;
    }

    private static function clearConditionalFields(mixed $state, Set $set): void
    {
        if (! self::stageMatches($state, DealStage::Lost)) {
            $set('lost_reason', null);
        }

        if (! self::stageMatches($state, DealStage::Won)) {
            $set('project_id', null);
        }
    }

    private static function formatCurrency(mixed $state): string
    {
        return strtoupper(is_scalar($state) ? strval($state) : 'EUR');
    }

    private static function stageMatches(mixed $state, DealStage $stage): bool
    {
        return $state === $stage || $state === $stage->value;
    }
}
