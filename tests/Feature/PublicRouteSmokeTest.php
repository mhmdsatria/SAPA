<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicRouteSmokeTest extends TestCase
{
    public function test_login_page_is_available(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Masuk');
    }
}
