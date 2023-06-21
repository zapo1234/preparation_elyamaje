<?php

namespace Tests\Feature;

use Tests\TestCase;


class UserTest extends TestCase
{

    public function test_site_unlogged()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

}
