<?php

declare(strict_types=1);

use App\Http\Controllers\SwitchLocaleController;
use App\Support\Localization;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::post('/locale/{locale}', SwitchLocaleController::class)
    ->whereIn('locale', Localization::supportedLocales())
    ->name('locale.switch')
;

Route::get('/', function (): View {
    return view('welcome');
});
