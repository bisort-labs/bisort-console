<?php

declare(strict_types=1);

use App\Support\Localization;

it('translates leaf keys into strings', function (): void {
    expect(Localization::translate('common.language'))->toBe('Language');
});

it('throws when a translation key resolves to a non-string value', function (): void {
    expect(fn (): string => Localization::translate('fields'))
        ->toThrow(RuntimeException::class, 'Translation [fields] must resolve to a string.')
    ;
});

it('returns an empty supported locale list when config is invalid', function (): void {
    config(['app.supported_locales' => 'de']);

    expect(Localization::supportedLocales())->toBe([]);
});

it('returns configured supported locales and filters invalid values', function (): void {
    config([
        'app.supported_locales' => [
            'en',
            1,
            null,
            'de',
        ],
    ]);

    expect(Localization::supportedLocales())->toBe([
        'en',
        'de',
    ]);
});

it('returns the configured default locale and falls back to english', function (): void {
    config(['app.locale' => 'de']);

    expect(Localization::defaultLocale())->toBe('de');
    config(['app.locale' => 1]);
    expect(Localization::defaultLocale())->toBe('en');
});
