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
            'LARAVEL_SURVEY_PREFIX_URL' => 'testing',
        ]);

        $url = TestsHelper::getRoutePath('home');

        $response = $this->followingRedirects()->call('GET', $url);

        $response->assertStatus(200);

        $dom = new \DOMDocument();

        @$dom->loadHTML($response->content());

        $forms = $dom->getElementsByTagName('form');

        $this->assertInstanceOf(\DOMNodeList::class, $forms);

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
    }

    public function testUpdateMissingConfigsIncorrectly()
    {
        $url = TestsHelper::getRoutePath('setup-update-missing-configs');

        $data = [
            'PUSHER_ENABLED' => 'true',
            'PUSHER_APP_ID' => 'testing',
            'PUSHER_APP_KEY' => 'testing',
            'PUSHER_APP_SECRET' => 'testing',
            'PUSHER_APP_CLUSTER' => 'testing',

            'GOOGLE_RECAPTCHA_ENABLED' => 'true',
            'GOOGLE_RECAPTCHA_SITE_SECRET' => 'testing',
            'GOOGLE_RECAPTCHA_SITE_KEY' => 'testing',

            'LARAVEL_SURVEY_PREFIX_URL' => 'testing'
        ];

        $response = $this->call('POST', $url, $data);

        $response->assertStatus(302);

        $this->assertEquals(route('home'), $response->headers->get('Location'));
    }

    public function testUpdateMissingConfigs()
    {
        $url = TestsHelper::getRoutePath('setup-update-missing-configs');

        $data = [
            'PUSHER_ENABLED' => 'false',
            'PUSHER_APP_ID' => '',
            'PUSHER_APP_KEY' => '',
            'PUSHER_APP_SECRET' => '',
            'PUSHER_APP_CLUSTER' => '',

            'GOOGLE_RECAPTCHA_ENABLED' => 'false',
            'GOOGLE_RECAPTCHA_SITE_SECRET' => '',
            'GOOGLE_RECAPTCHA_SITE_KEY' => '',

            'LARAVEL_SURVEY_PREFIX_URL' => self::$laravel_url_prefix,
        ];

        $response = $this->call('POST', $url, $data);

        $response->assertStatus(302);

        $redirect_url = $response->headers->get('Location');

        $this->assertEquals(route('home'), $redirect_url);

        $response = $this->call('GET', $redirect_url);

        $dom = new \DOMDocument();

        @$dom->loadHTML($response->content());

        $xpath = new \DOMXPath($dom);

        $alerts = $xpath->query('//*[contains(@class, "alert-success")]');

        $this->assertInstanceOf(\DOMNodeList::class, $alerts);

        $this->assertEquals(1, $alerts->length);

        $alert = $alerts->item(0);

        $this->assertEquals('Success: Configurations updated successfully.', trim($alert->textContent));
    }
}
