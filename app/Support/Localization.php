<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

class Localization
{
    /**
     * @param  array<string, bool|float|int|string|null>  $replace
     */
    public static function translate(string $key, array $replace = []): string
    {
        $translation = __($key, $replace);

        if (is_string($translation) === false) {
            throw new RuntimeException("Translation [{$key}] must resolve to a string.");
        }

        return $translation;
    }

    /**
     * @return list<string>
     */
    public static function supportedLocales(): array
    {
        $configuredLocales = config('app.supported_locales', []);

        if (is_array($configuredLocales) === false) {
            return [];
        }

        $supportedLocales = [];

        foreach ($configuredLocales as $configuredLocale) {
            if (is_string($configuredLocale)) {
                $supportedLocales[] = $configuredLocale;
            }
        }

        return $supportedLocales;
    }

    public static function defaultLocale(): string
    {
        $locale = config('app.locale', 'en');

        return is_string($locale) ? $locale : 'en';
    }
}
