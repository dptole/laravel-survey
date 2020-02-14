<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\TestsHelper;

class DashboardTest extends TestCase
{
    public function testEnterDashboard()
    {
        $response = $this->call(
      'GET',
      TestsHelper::getRoutePath('dashboard'),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

        $response->assertStatus(200);
    }
}
