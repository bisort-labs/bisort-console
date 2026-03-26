<?php

declare(strict_types=1);

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
});

it('configures the lead form through the resource', function (): void {
    $schema = LeadResource::form(new Schema);

    $componentNames = array_map(static function (mixed $component): string {
        if ($component instanceof TextInput || $component instanceof Select) {
            return $component->getName();
        }

        throw new RuntimeException('Expected lead form components to be text inputs or selects.');
    }, $schema->getComponents());

    expect($schema)->toBeInstanceOf(Schema::class)
        ->and($componentNames)->toBe([
            'name',
            'email',
            'company',
            'street',
            'city',
            'state',
            'zip',
            'country',
            'phone',
            'source',
            'status',
            'owner_id',
        ])
    ;
});

it('configures the leads table through the resource', function (): void {
    $table = LeadResource::table(new Table(new ListLeads));

    $columnNames = array_values(array_map(static function (mixed $column): string {
        if ($column instanceof TextColumn) {
            return $column->getName();
        }

        throw new RuntimeException('Expected lead table columns to be text columns.');
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

        throw new RuntimeException('Expected lead table record actions to have string names.');
    }, $table->getRecordActions()));

    $toolbarActions = array_values($table->getToolbarActions());

    expect($table)->toBeInstanceOf(Table::class)
        ->and($columnNames)->toBe([
            'name',
            'company',
            'status',
            'source',
            'email',
            'phone',
            'owner.name',
            'created_at',
            'updated_at',
            'deleted_at',
        ])
        ->and($filterNames)->toBe([
            'trashed',
            'status',
            'source',
            'owner',
        ])
        ->and($recordActionNames)->toBe([
            'view',
            'edit',
        ])
        ->and($toolbarActions)->toHaveCount(1)
        ->and($toolbarActions[0] ?? null)->toBeInstanceOf(BulkActionGroup::class)
    ;
});

it('defines translated lead resource metadata and pages', function (): void {
    $pages = LeadResource::getPages();

    expect(LeadResource::getModelLabel())->toBe(__('navigation.resources.lead.singular'))
        ->and(LeadResource::getPluralModelLabel())->toBe(__('navigation.resources.lead.plural'))
        ->and(LeadResource::getNavigationGroup())->toBe(__('navigation.groups.management'))
        ->and(LeadResource::getRelations())->toBe([])
        ->and(array_keys($pages))->toBe([
            'index',
            'create',
            'view',
            'edit',
        ])
    ;
});

it('removes the soft deleting scope from route binding queries', function (): void {
    $lead = Lead::query()->create([
        'name' => 'Deleted Prospect',
        'email' => 'deleted@example.com',
        'source' => LeadSource::Other->value,
        'status' => LeadStatus::Unqualified->value,
    ]);

    $lead->delete();

    $query = LeadResource::getRecordRouteBindingEloquentQuery();

    expect($query->firstWhere('id', $lead->getKey())?->is($lead))->toBeTrue();
});

it('defines the expected list and edit header actions', function (): void {
    $listMethod = new ReflectionMethod(ListLeads::class, 'getHeaderActions');
    $listMethod->setAccessible(true);

    $editMethod = new ReflectionMethod(EditLead::class, 'getHeaderActions');
    $editMethod->setAccessible(true);

    /** @var array<int, Action> $listActions */
    $listActions = $listMethod->invoke(new ListLeads);
    /** @var array<int, Action> $editActions */
    $editActions = $editMethod->invoke(new EditLead);

    expect(array_map(static function (Action $action): string {
        $name = $action->getName();

        if (is_string($name)) {
            return $name;
        }

        throw new RuntimeException('Expected list actions to have string names.');
    }, $listActions))
        ->toBe(['create'])
        ->and(array_map(static function (Action $action): string {
            $name = $action->getName();

            if (is_string($name)) {
                return $name;
            }

            throw new RuntimeException('Expected edit actions to have string names.');
        }, $editActions))
        ->toBe([
            'view',
            'delete',
            'forceDelete',
            'restore',
        ])
    ;
});
