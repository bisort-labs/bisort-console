<?php

declare(strict_types=1);

use function Pest\Laravel\from;
use function Pest\Laravel\withSession;

it('switches locale and persists it in session', function (): void {
    from('/console/login')
        ->post(route('locale.switch', ['locale' => 'de']))
        ->assertRedirect('/console/login')
        ->assertSessionHas('locale', 'de')
    ;
});

it('renders the login locale switcher in german', function (): void {
    withSession(['locale' => 'de'])
        ->get('/console/login')
        ->assertOk()
        ->assertSeeText('Sprache')
        ->assertSeeText('English')
        ->assertSeeText('Deutsch')
        ->assertSee('data-locale="de"', escape: false)
        ->assertSee('data-active="true"', escape: false)
    ;
});
