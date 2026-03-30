@php
    $supportedLocales = config('app.supported_locales', []);
    $currentLocale = app()->currentLocale();
@endphp

@if ($supportedLocales !== [])
    <div class="mt-4 flex flex-col gap-2">
        <span class="text-sm font-medium text-gray-950 dark:text-white">
            {{ __('common.language') }}
        </span>

        <div class="flex flex-wrap items-center gap-2">
            @foreach ($supportedLocales as $locale)
                @php($isCurrentLocale = $locale === $currentLocale)

                <form method="POST" action="{{ route('locale.switch', ['locale' => $locale]) }}">
                    @csrf

                    <x-filament::button
                        :color="$isCurrentLocale ? 'primary' : 'gray'"
                        :outlined="! $isCurrentLocale"
                        aria-pressed="{{ $isCurrentLocale ? 'true' : 'false' }}"
                        data-active="{{ $isCurrentLocale ? 'true' : 'false' }}"
                        data-locale="{{ $locale }}"
                        size="sm"
                        type="submit"
                    >
                        {{ __("common.locales.{$locale}") }}
                    </x-filament::button>
                </form>
            @endforeach
        </div>
    </div>
@endif
