<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use App\Observers\CustomerObserver;
use App\Observers\DealObserver;
use App\Observers\LeadObserver;
use Illuminate\Support\ServiceProvider;
use Override;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Customer::observe(CustomerObserver::class);
        Deal::observe(DealObserver::class);
        Lead::observe(LeadObserver::class);
    }
}
