<?php

namespace Tests\Feature;

use App\Helper;
use Tests\TestCase;

class HomeTest extends TestCase
{
    public function testRoot()
    {
        $old_prefix = Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL');
        Helper::updateDotEnvFileVars(['LARAVEL_SURVEY_PREFIX_URL' => '']);

        $response = $this->get('/');
        $response->assertStatus(200);

        Helper::updateDotEnvFileVars(['LARAVEL_SURVEY_PREFIX_URL' => $old_prefix]);
    }

    public function testSubRoot()
    {
        $response = $this->get('/');
        $response->assertStatus(302);
    }
}
