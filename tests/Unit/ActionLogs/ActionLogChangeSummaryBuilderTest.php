<?php

declare(strict_types=1);

use App\Enums\CustomerType;
use App\Enums\DealStage;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\ClientProject;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use App\Services\ActionLog\ActionLogChangeSummaryBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds a lead change summary with localized field labels and values', function (): void {
    $currentOwner = User::factory()->create(['name' => 'Morgan Lee']);
    $nextOwner = User::factory()->create(['name' => 'Jamie Fox']);

    $lead = null;

    Lead::withoutEvents(function () use (&$lead, $currentOwner): void {
        $createdLead = Lead::query()->create([
            'name' => 'Northwind Prospect',
            'email' => 'northwind@example.com',
            'company' => 'Northwind GmbH',
            'phone' => '+49 30 123456',
            'source' => LeadSource::ColdOutreach->value,
            'status' => LeadStatus::New->value,
        ]);

        $createdLead->forceFill(['owner_id' => $currentOwner->getKey()])->save();
        $lead = $createdLead->refresh();
    });

    if (! $lead instanceof Lead) {
        throw new RuntimeException('Expected a lead instance.');
    }

    $lead->forceFill([
        'name' => 'Northwind Qualified Prospect',
        'status' => LeadStatus::Qualified->value,
        'owner_id' => $nextOwner->getKey(),
    ]);

    $summary = app(ActionLogChangeSummaryBuilder::class)->build($lead);

    expect($summary)->not->toBeNull()
        ->and($summary?->title)->toBe('Lead details updated')
        ->and($summary?->body)->toBe(implode("\n", [
            'Name: Northwind Prospect -> Northwind Qualified Prospect',
            'Status: New -> Qualified',
            'Owner: Morgan Lee -> Jamie Fox',
        ]))
    ;
});

it('builds a deal change summary with relations, money, dates, and placeholders', function (): void {
    $lead = Lead::factory()->create(['name' => 'Northwind Prospect']);
    $nextLead = Lead::factory()->create(['name' => 'Helios Prospect']);
    $currentOwner = User::factory()->create(['name' => 'Morgan Lee']);
    $nextOwner = User::factory()->create(['name' => 'Jamie Fox']);
    $project = ClientProject::query()->create([
        'name' => 'Northwind Website Refresh',
        'slug' => 'northwind-website-refresh',
        'description' => 'A redesign and rebuild of the public marketing site.',
        'is_active' => true,
    ]);

    $deal = null;

    Deal::withoutEvents(function () use (&$deal, $currentOwner, $lead, $project): void {
        $deal = Deal::factory()->for($lead)->create([
            'project_id' => $project->getKey(),
            'stage' => DealStage::Won,
            'expected_value_cents' => 120050,
            'currency' => 'EUR',
            'probability' => 40,
            'close_date' => '2026-06-15',
            'owner_id' => $currentOwner->getKey(),
            'notes' => null,
        ]);
    });

    if (! $deal instanceof Deal) {
        throw new RuntimeException('Expected a deal instance.');
    }

    $deal->fill([
        'lead_id' => $nextLead->getKey(),
        'project_id' => null,
        'stage' => DealStage::Lost->value,
        'expected_value_cents' => 250075,
        'currency' => 'USD',
        'probability' => null,
        'close_date' => null,
        'lost_reason' => 'Budget constraints',
        'notes' => "Need a tighter scope\nfor the next revision.",
        'owner_id' => $nextOwner->getKey(),
    ]);

    $summary = app(ActionLogChangeSummaryBuilder::class)->build($deal);

    expect($summary)->not->toBeNull()
        ->and($summary?->title)->toBe('Deal details updated')
        ->and($summary?->body)->toBe(implode("\n", [
            'Lead: Northwind Prospect -> Helios Prospect',
            'Project: Northwind Website Refresh -> -',
            'Stage: Won -> Lost',
            'Expected value: EUR 1200.50 -> USD 2500.75',
            'Currency: EUR -> USD',
            'Probability: 40% -> -',
            'Close date: 2026-06-15 -> -',
            'Lost reason: - -> Budget constraints',
            'Notes: - -> Need a tighter scope for the next revision.',
            'Owner: Morgan Lee -> Jamie Fox',
        ]))
    ;
});

it('builds a customer change summary with enum, boolean, and billing address values', function (): void {
    $customer = null;

    Customer::withoutEvents(function () use (&$customer): void {
        $customer = Customer::query()->create([
            'name' => 'Northwind GmbH',
            'type' => CustomerType::B2B->value,
            'email' => 'billing@northwind.example',
            'phone' => '+49 30 123456',
            'country_code' => 'DE',
            'vat_id' => 'DE123456789',
            'tax_number' => 'TAX-12345678',
            'is_vat_exempt' => false,
            'vat_exemption_reason' => null,
            'billing_address' => [
                'street' => 'Unter den Linden 1',
                'city' => 'Berlin',
                'state' => 'Berlin',
                'zip' => '10117',
                'country' => 'Germany',
            ],
        ]);
    });

    if (! $customer instanceof Customer) {
        throw new RuntimeException('Expected a customer instance.');
    }

    $customer->fill([
        'type' => CustomerType::B2C->value,
        'country_code' => 'AT',
        'vat_id' => null,
        'tax_number' => 'TAX-87654321',
        'is_vat_exempt' => true,
        'vat_exemption_reason' => 'Reverse charge',
        'billing_address' => [
            'street' => 'Kärntner Ring 1',
            'city' => 'Vienna',
            'state' => 'Vienna',
            'zip' => '1010',
            'country' => 'Austria',
        ],
    ]);

    $summary = app(ActionLogChangeSummaryBuilder::class)->build($customer);

    expect($summary)->not->toBeNull()
        ->and($summary?->title)->toBe('Customer details updated')
        ->and($summary?->body)->toBe(implode("\n", [
            'Type: B2B -> B2C',
            'Country code: DE -> AT',
            'VAT ID: DE123456789 -> -',
            'Tax number: TAX-12345678 -> TAX-87654321',
            'VAT exempt: No -> Yes',
            'VAT exemption reason: - -> Reverse charge',
            'Billing address: Unter den Linden 1, Berlin, Berlin, 10117, Germany -> Kärntner Ring 1, Vienna, Vienna, 1010, Austria',
        ]))
    ;
});

it('returns no summary when normalized values did not materially change', function (): void {
    $lead = Lead::factory()->create();

    $deal = null;

    Deal::withoutEvents(function () use (&$deal, $lead): void {
        $deal = Deal::factory()->for($lead)->create([
            'currency' => 'EUR',
        ]);
    });

    if (! $deal instanceof Deal) {
        throw new RuntimeException('Expected a deal instance.');
    }

    $deal->fill([
        'currency' => 'eur',
    ]);

    expect(app(ActionLogChangeSummaryBuilder::class)->build($deal))->toBeNull();
});
