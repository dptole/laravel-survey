<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\TestsHelper;

class DashboardTest extends TestCase
{
    public function testEnterDashboard()
    {
        $url = TestsHelper::getRoutePath('dashboard');

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }
}
