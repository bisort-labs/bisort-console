<?php

declare(strict_types=1);

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('console'));
});

it('configures the customer form through the resource', function (): void {
    $schema = CustomerResource::form(new Schema(new CreateCustomer));

    $sections = $schema->getComponents();

    $sectionHeadings = array_map(static function (mixed $section): string {
        if ($section instanceof Section) {
            $heading = $section->getHeading();

            if (is_string($heading)) {
                return $heading;
            }
        }

        throw new RuntimeException('Expected customer form components to be sections with string headings.');
    }, $sections);

    $fieldNames = array_map(static function (mixed $component): string {
        if ($component instanceof Select || $component instanceof TextInput || $component instanceof Textarea || $component instanceof Toggle) {
            return $component->getName();
        }

        throw new RuntimeException('Expected customer form components to be supported input components.');
    }, array_merge(...array_map(static function (mixed $section): array {
        if ($section instanceof Section) {
            return $section->getChildComponents();
        }

        throw new RuntimeException('Expected customer form schema to contain section components.');
    }, $sections)));

    expect($schema)->toBeInstanceOf(Schema::class)
        ->and($sectionHeadings)->toBe([
            __('common.sections.identity'),
            __('common.sections.contact'),
            __('common.sections.tax'),
            __('common.sections.billing_address'),
        ])
        ->and($fieldNames)->toBe([
            'name',
            'type',
            'email',
            'phone',
            'country_code',
            'vat_id',
            'tax_number',
            'is_vat_exempt',
            'billing_address.street',
            'billing_address.city',
            'billing_address.state',
            'billing_address.zip',
            'billing_address.country',
        ])
    ;
});

it('configures the customers table through the resource', function (): void {
    $table = CustomerResource::table(new Table(new ListCustomers));

    $columnNames = array_values(array_map(static function (mixed $column): string {
        if ($column instanceof TextColumn) {
            return $column->getName();
        }

        throw new RuntimeException('Expected customer table columns to be text columns.');
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

        throw new RuntimeException('Expected customer table record actions to have string names.');
    }, $table->getRecordActions()));

    $toolbarActions = array_values($table->getToolbarActions());

    expect($table)->toBeInstanceOf(Table::class)
        ->and($columnNames)->toBe([
            'name',
            'type',
            'country_code',
            'vat_id',
            'email',
            'created_at',
            'updated_at',
            'deleted_at',
        ])
        ->and($filterNames)->toBe([
            'trashed',
            'type',
            'country_code',
            'is_vat_exempt',
        ])
        ->and($recordActionNames)->toBe([
            'view',
            'edit',
        ])
        ->and($toolbarActions)->toHaveCount(1)
        ->and($toolbarActions[0] ?? null)->toBeInstanceOf(BulkActionGroup::class)
    ;
});

it('defines translated customer resource metadata and pages', function (): void {
    $pages = CustomerResource::getPages();

    expect(CustomerResource::getModelLabel())->toBe(__('navigation.resources.customer.singular'))
        ->and(CustomerResource::getPluralModelLabel())->toBe(__('navigation.resources.customer.plural'))
        ->and(CustomerResource::getNavigationGroup())->toBe(__('navigation.groups.management'))
        ->and(CustomerResource::getRelations())->toBe([])
        ->and(array_keys($pages))->toBe([
            'index',
            'create',
            'view',
            'edit',
        ])
    ;
});

it('removes the soft deleting scope from customer route binding queries', function (): void {
    $customer = Customer::query()->create([
        'name' => 'Deleted Customer',
        'type' => 'b2b',
    ]);

    $customer->delete();

    $query = CustomerResource::getRecordRouteBindingEloquentQuery();

    expect($query->firstWhere('id', $customer->getKey())?->is($customer))->toBeTrue();
});

it('defines the expected list and edit header actions for customers', function (): void {
    $listMethod = new ReflectionMethod(ListCustomers::class, 'getHeaderActions');
    $listMethod->setAccessible(true);

    $editMethod = new ReflectionMethod(EditCustomer::class, 'getHeaderActions');
    $editMethod->setAccessible(true);

    /** @var array<int, Action> $listActions */
    $listActions = $listMethod->invoke(new ListCustomers);
    /** @var array<int, Action> $editActions */
    $editActions = $editMethod->invoke(new EditCustomer);

    expect(array_map(static function (Action $action): string {
        $name = $action->getName();

        if (is_string($name)) {
            return $name;
        }

        throw new RuntimeException('Expected customer list actions to have string names.');
    }, $listActions))
        ->toBe(['create'])
        ->and(array_map(static function (Action $action): string {
            $name = $action->getName();

            if (is_string($name)) {
                return $name;
            }

            throw new RuntimeException('Expected customer edit actions to have string names.');
        }, $editActions))
        ->toBe([
            'view',
            'delete',
            'forceDelete',
            'restore',
        ])
    ;
});
