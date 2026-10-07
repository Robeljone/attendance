<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_shows_portal_chooser_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(__('Choose your portal'));
    }
}
