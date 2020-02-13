<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Tests\TestsHelper;

class DashboardTestCase extends TestCase
{
  public function testEnterDashboard() {
    $response = $this->call(
      'GET',
      TestsHelper::getRoutePath('dashboard'),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }
}
