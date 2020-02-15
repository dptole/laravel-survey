<?php

namespace Tests\Feature;

use App\Helper;
use Tests\TestCase;
use Tests\TestsHelper;

class SetupTest extends TestCase
{
    public static $laravel_url_prefix = '';

    public function testShowMissingConfigs()
    {
        self::$laravel_url_prefix = Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL');

        Helper::updateDotEnvFileVars([
            'PUSHER_ENABLED'            => 'true',
            'GOOGLE_RECAPTCHA_ENABLED'  => 'true',
            'LARAVEL_SURVEY_PREFIX_URL' => 'invalid',
        ]);

        $url = TestsHelper::getRoutePath('home');

        $response = $this->followingRedirects()->call('GET', $url);

        $response->assertStatus(200);

        $dom = new \DOMDocument();

        @$dom->loadHTML($response->content());

        $forms = $dom->getElementsByTagName('form');

        $this->assertEquals(1, $forms->length);

        $attr_action = $forms->item(0)->attributes->getNamedItem('action');

        $this->assertInstanceOf(\DOMAttr::class, $attr_action);

        $this->assertEquals(route('setup-update-missing-configs'), $attr_action->nodeValue);
    }

    public function testShowMessageBlockingNavigationIfMissingConfigs()
    {
        $url = TestsHelper::getRoutePath('login');

        $response = $this->call('GET', $url);

        $response->assertStatus(302);

        $this->assertEquals(route('home'), $response->headers->get('Location'));

        Helper::updateDotEnvFileVars([
            'PUSHER_ENABLED'            => 'false',
            'GOOGLE_RECAPTCHA_ENABLED'  => 'false',
            'LARAVEL_SURVEY_PREFIX_URL' => self::$laravel_url_prefix,
        ]);
    }

    public function testUpdateMissingConfigsIncorrectly()
    {
        $this->markTestIncomplete();
    }

    public function testUpdateMissingConfigs()
    {
        $this->markTestIncomplete();
    }
}
