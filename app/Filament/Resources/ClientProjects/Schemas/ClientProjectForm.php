<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientProjects\Schemas;

use App\Services\Localization;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ClientProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(Localization::translate('common.sections.overview'))
                ->schema([
                    TextInput::make('name')
                        ->label(Localization::translate('fields.name'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(self::liveUpdateSlug(...)),
                    TextInput::make('slug')
                        ->label(Localization::translate('fields.slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Textarea::make('description')
                        ->label(Localization::translate('fields.description'))
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label(Localization::translate('fields.active'))
                        ->required()
                        ->default(false),
                ]),
        ]);
    }

    private static function liveUpdateSlug(string $state, Set $set): mixed
    {
        return $set('slug', Str::slug($state));
    }
}
