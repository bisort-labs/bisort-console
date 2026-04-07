<?php

declare(strict_types=1);

use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\Deals\Pages\CreateDeal;
use App\Filament\Resources\Deals\Pages\EditDeal;
use App\Filament\Resources\Deals\Pages\ListDeals;
use App\Models\Deal;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
});

it('configures the deal form through the resource', function (): void {
    $schema = DealResource::form(new Schema(new CreateDeal));

    $sections = $schema->getComponents();

    $sectionHeadings = array_map(static function (mixed $section): string {
        if ($section instanceof Section) {
            $heading = $section->getHeading();

            if (is_string($heading)) {
                return $heading;
            }
        }

        throw new RuntimeException('Expected deal form components to be sections with string headings.');
    }, $sections);

    $fieldNames = array_map(static function (mixed $component): string {
        if ($component instanceof DatePicker || $component instanceof Select || $component instanceof TextInput || $component instanceof Textarea) {
            return $component->getName();
        }

        throw new RuntimeException('Expected deal form components to be supported input components.');
    }, array_merge(...array_map(static function (mixed $section): array {
        if ($section instanceof Section) {
            return $section->getChildComponents();
        }

        throw new RuntimeException('Expected deal form schema to contain section components.');
    }, $sections)));

    expect($schema)->toBeInstanceOf(Schema::class)
        ->and($sectionHeadings)->toBe([
            __('common.sections.overview'),
            __('common.sections.deal_details'),
        ])
        ->and($fieldNames)->toBe([
            'lead_id',
            'title',
            'stage',
            'expected_value_cents',
            'currency',
            'probability',
            'close_date',
            'owner_id',
            'notes',
        ])
    ;
});

it('configures the deals table through the resource', function (): void {
    $table = DealResource::table(new Table(new ListDeals));

    $columnNames = array_values(array_map(static function (mixed $column): string {
        if ($column instanceof TextColumn) {
            return $column->getName();
        }

        throw new RuntimeException('Expected deal table columns to be text columns.');
    }, $table->getColumns()));

    $filterNames = array_values(array_map(
        static fn (BaseFilter $filter): string => $filter->getName(),
        $table->getFilters(),
    ));

    $recordActionNames = array_values(array_map(static function (mixed $action): string {
        if ($action instanceof Action) {
            $name = $action->getName();

            if (is_string($name)) {
                return $name;
            }
        }

        throw new RuntimeException('Expected deal table record actions to have string names.');
    }, $table->getRecordActions()));

    $toolbarActions = array_values($table->getToolbarActions());

    expect($table)->toBeInstanceOf(Table::class)
        ->and($columnNames)->toBe([
            'title',
            'lead.name',
            'expected_value_cents',
            'stage',
            'close_date',
            'owner.name',
            'updated_at',
            'created_at',
            'deleted_at',
        ])
        ->and($filterNames)->toBe([
            'trashed',
            'stage',
            'owner',
            'lead',
        ])
        ->and($recordActionNames)->toBe([
            'view',
            'edit',
        ])
        ->and($toolbarActions)->toHaveCount(1)
        ->and($toolbarActions[0] ?? null)->toBeInstanceOf(BulkActionGroup::class)
    ;
});

it('defines translated deal resource metadata and pages', function (): void {
    $pages = DealResource::getPages();

    expect(DealResource::getModelLabel())->toBe(__('navigation.resources.deal.singular'))
        ->and(DealResource::getPluralModelLabel())->toBe(__('navigation.resources.deal.plural'))
        ->and(DealResource::getNavigationGroup())->toBe(__('navigation.groups.management'))
        ->and(DealResource::getRelations())->toBe([])
        ->and(array_keys($pages))->toBe([
            'index',
            'create',
            'view',
            'edit',
        ])
    ;
});

it('removes the soft deleting scope from deal route binding queries', function (): void {
    $lead = Lead::factory()->create();

    $deal = Deal::factory()->for($lead)->create();
    $deal->delete();

    $query = DealResource::getRecordRouteBindingEloquentQuery();

    expect($query->firstWhere('id', $deal->getKey())?->is($deal))->toBeTrue();
});

it('defines the expected list and edit header actions for deals', function (): void {
    $listMethod = new ReflectionMethod(ListDeals::class, 'getHeaderActions');
    $listMethod->setAccessible(true);

    $editMethod = new ReflectionMethod(EditDeal::class, 'getHeaderActions');
    $editMethod->setAccessible(true);

    /** @var array<int, Action> $listActions */
    $listActions = $listMethod->invoke(new ListDeals);
    /** @var array<int, Action> $editActions */
    $editActions = $editMethod->invoke(new EditDeal);

    expect(array_map(static function (Action $action): string {
        $name = $action->getName();

        if (is_string($name)) {
            return $name;
        }

        throw new RuntimeException('Expected deal list actions to have string names.');
    }, $listActions))
        ->toBe(['create'])
        ->and(array_map(static function (Action $action): string {
            $name = $action->getName();

            if (is_string($name)) {
                return $name;
            }

            throw new RuntimeException('Expected deal edit actions to have string names.');
        }, $editActions))
        ->toBe([
            'view',
            'delete',
            'forceDelete',
            'restore',
        ])
    ;
});
