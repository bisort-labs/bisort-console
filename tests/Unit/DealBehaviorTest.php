<?php

declare(strict_types=1);

use App\Enums\DealStage;
use App\Models\ClientProject;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use App\Support\Deals\DealMoney;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class);

it('casts deal attributes and provides stage labels and colors', function (): void {
    $deal = (new Deal)->setRawAttributes([
        'stage' => DealStage::Negotiation->value,
        'close_date' => '2026-05-01',
        'expected_value_cents' => 125050,
        'probability' => 65,
    ]);

    expect($deal->stage)->toBe(DealStage::Negotiation)
        ->and($deal->close_date)->toBeInstanceOf(Carbon::class)
        ->and($deal->expected_value_cents)->toBe(125050)
        ->and($deal->probability)->toBe(65)
        ->and(DealStage::New->getLabel())->toBe('New')
        ->and(DealStage::New->getColor())->toBe('gray')
        ->and(DealStage::ProposalSent->getLabel())->toBe('Proposal sent')
        ->and(DealStage::ProposalSent->getColor())->toBe('warning')
        ->and(DealStage::Negotiation->getLabel())->toBe('Negotiation')
        ->and(DealStage::Negotiation->getColor())->toBe('primary')
        ->and(DealStage::Won->getLabel())->toBe('Won')
        ->and(DealStage::Won->getColor())->toBe('success')
        ->and(DealStage::Lost->getLabel())->toBe('Lost')
        ->and(DealStage::Lost->getColor())->toBe('danger')
    ;
});

it('converts amounts between decimal strings and cents without float drift', function (): void {
    expect(DealMoney::amountToCents('1200'))->toBe(120000)
        ->and(DealMoney::amountToCents('1200.50'))->toBe(120050)
        ->and(DealMoney::amountToCents('1200,75'))->toBe(120075)
        ->and(DealMoney::centsToAmount(120050))->toBe('1200.50')
    ;
});

it('defines the expected deal relation objects', function (): void {
    $deal = new Deal;
    $lead = new Lead;
    $user = new User;
    $project = new ClientProject;

    expect($deal->lead())->toBeInstanceOf(BelongsTo::class)
        ->and($deal->owner())->toBeInstanceOf(BelongsTo::class)
        ->and($deal->project())->toBeInstanceOf(BelongsTo::class)
        ->and($deal->actionLogs())->toBeInstanceOf(MorphMany::class)
        ->and($lead->deals())->toBeInstanceOf(HasMany::class)
        ->and($user->deals())->toBeInstanceOf(HasMany::class)
        ->and($project->deals())->toBeInstanceOf(HasMany::class)
    ;
});
