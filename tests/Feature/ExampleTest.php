<?php

use Tests\TestCase;

test('the application returns a successful response', function (): void {
    /** @var TestCase $this */
    $this->get('/')->assertStatus(200);
});
