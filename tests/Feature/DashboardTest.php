<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Tests\TestsHelper;
use App\Helper;

class DashboardTest extends TestCase
{
  public function testEnterDashboard() {
    $response = $this->call(
      'GET',
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/dashboard',
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }
}
