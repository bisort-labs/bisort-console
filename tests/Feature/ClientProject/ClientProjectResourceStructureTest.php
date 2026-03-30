<?php

declare(strict_types=1);

use App\Filament\Resources\ClientProjects\ClientProjectResource;
use App\Filament\Resources\ClientProjects\Pages\CreateClientProject;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
});

it('configures the client project form inside an overview section', function (): void {
    $schema = ClientProjectResource::form(new Schema(new CreateClientProject));
    $sections = $schema->getComponents();

    $sectionHeadings = array_map(static function (mixed $section): string {
        if ($section instanceof Section) {
            $heading = $section->getHeading();

            if (is_string($heading)) {
                return $heading;
            }
        }

        throw new RuntimeException('Expected client project form components to be sections with string headings.');
    }, $sections);

    $fieldNames = array_map(static function (mixed $component): string {
        if ($component instanceof TextInput || $component instanceof Textarea || $component instanceof Toggle) {
            return $component->getName();
        }

        throw new RuntimeException('Expected client project form fields to be text inputs, textareas, or toggles.');
    }, array_merge(...array_map(static function (mixed $section): array {
        if ($section instanceof Section) {
            return $section->getChildComponents();
        }

        throw new RuntimeException('Expected client project schema to contain section components.');
    }, $sections)));

    expect($schema)->toBeInstanceOf(Schema::class)
        ->and($sectionHeadings)->toBe([
            __('common.sections.overview'),
        ])
        ->and($fieldNames)->toBe([
            'name',
            'slug',
            'description',
            'is_active',
        ])
    ;
});
