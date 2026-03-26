<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Localization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SwitchLocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, Localization::supportedLocales(), true), 404);

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        return back();
    }
}
