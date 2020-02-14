<?php

namespace Tests\Feature;

use App\Helper;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\TestsHelper;
use Webpatser\Uuid\Uuid;

class AuthTest extends TestCase
{
    public function testRegister()
    {
        $url = TestsHelper::getRoutePath('register.create');

        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct, $user_invalid_email) = $ui;

            $response1 = $this->followingRedirects()->call('POST', $url, $user_input1);

            $response1->assertStatus(200);

            $response2 = $this->followingRedirects()->call('POST', $url, $user_input2);

            $response2->assertStatus(200);
        }
    }

    public function testRegisterInvalidEmail()
    {
        $url = TestsHelper::getRoutePath('register.create');

        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct, $user_invalid_email) = $ui;

            $response2 = $this->followingRedirects()->call('POST', $url, $user_invalid_email);

            $response2->assertStatus(200);
        }
    }

    public function testUserAfterRegistered()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input, $user_input2, $user_recaptcha, $user_direct) = $ui;

            $users = User::where('email', '=', $user_input['email'])->limit(1)->get();
            $this->assertCount(1, $users);
            $user_db = $users[0];

            TestsHelper::$shared_objects['auth']['logged_in'] = $user_db;

            $this->assertIsNumeric($user_db->id);
            $this->assertEquals($user_input['name'], $user_db->name);
            $this->assertEquals($user_input['email'], $user_db->email);
            $this->assertTrue(password_verify($user_input['password'], $user_db->password));
            $this->assertTrue(Uuid::validate($user_db->uuid));
            $this->assertNull($user_db->remember_token);
            $this->assertInstanceOf(Carbon::class, $user_db->created_at);
            $this->assertInstanceOf(Carbon::class, $user_db->updated_at);
            $this->assertEquals($user_db->updated_at.'', $user_db->created_at.'');
        }
    }

    public function testRegisterWithGoogleReCaptchaEnabled()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct, $user_invalid_email) = $ui;

            $GLOBALS['isGoogleReCaptchaEnabled'] = true;

            $e = Helper::getDotEnvFileRaw();

            $old_secret = Helper::getDotEnvFileVar('GOOGLE_RECAPTCHA_SITE_SECRET');

            $written_bytes = Helper::updateDotEnvFileVars(['GOOGLE_RECAPTCHA_SITE_SECRET' => $old_secret . '.']);

            $this->assertIsNumeric($written_bytes);

            $this->assertEquals(strlen($e) + 1, $written_bytes);

            $url = TestsHelper::getRoutePath('register.create');

            $response = $this->followingRedirects()->call('POST', $url, $user_recaptcha);

            $response->assertStatus(200);

            $written_bytes = Helper::updateDotEnvFileVars(['GOOGLE_RECAPTCHA_SITE_SECRET' => $old_secret]);

            $this->assertIsNumeric($written_bytes);

            $this->assertEquals(strlen($e), $written_bytes);

            unset($GLOBALS['isGoogleReCaptchaEnabled']);
        }
    }

    public function testNotRegisteredWithGoogleReCaptchaEnabled()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct, $user_invalid_email) = $ui;

            $users = User::where('email', '=', $user_recaptcha['email'])->limit(1)->get();
            $this->assertCount(0, $users);
        }
    }

    public function testLogin()
    {
        $url = TestsHelper::getRoutePath('login.create');

        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct, $user_invalid_email) = $ui;

            $response = $this->followingRedirects()->call('POST', $url, $user_input1);

            $response->assertStatus(200);

            $response = $this->followingRedirects()->call('POST', $url, $user_input2);

            $response->assertStatus(200);

            TestsHelper::storeLaravelSession($response);
        }
    }

    public function testLoginWithGoogleReCaptchaFailed()
    {
        $url = TestsHelper::getRoutePath('login.create');

        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct, $user_invalid_email) = $ui;

            $GLOBALS['isGoogleReCaptchaEnabled'] = true;
            $GLOBALS['googleReCaptchaFailed'] = true;

            $user_input1_recaptcha = $user_input1;

            $user_input1_recaptcha['g-recaptcha-response'] = '.';

            $response = $this->followingRedirects()->call('POST', $url, $user_input1_recaptcha);

            $response->assertStatus(200);

            $user_input2_recaptcha = $user_input2;

            $user_input2_recaptcha['g-recaptcha-response'] = '.';

            $response = $this->followingRedirects()->call('POST', $url, $user_input2_recaptcha);

            $response->assertStatus(200);

            unset($GLOBALS['isGoogleReCaptchaEnabled']);
            unset($GLOBALS['googleReCaptchaFailed']);
        }
    }

    public function testLoginWithGoogleReCaptchaSucceeded()
    {
        $url = TestsHelper::getRoutePath('login.create');

        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct, $user_invalid_email) = $ui;

            $GLOBALS['isGoogleReCaptchaEnabled'] = true;
            $GLOBALS['googleReCaptchaFailed'] = false;

            $user_input1_recaptcha = $user_input1;

            $user_input1_recaptcha['g-recaptcha-response'] = '.';

            $response = $this->followingRedirects()->call('POST', $url, $user_input1_recaptcha);

            $response->assertStatus(200);

            $user_input2_recaptcha = $user_input2;

            $user_input2_recaptcha['g-recaptcha-response'] = '.';

            $response = $this->followingRedirects()->call('POST', $url, $user_input2_recaptcha);

            $response->assertStatus(200);

            unset($GLOBALS['isGoogleReCaptchaEnabled']);
            unset($GLOBALS['googleReCaptchaFailed']);
        }
    }

    public function testLogout()
    {
        $url = TestsHelper::getRoutePath('logout');

        $cookies = ['laravel_session' => TestsHelper::$laravel_session];

        $response = $this->followingRedirects()->call('POST', $url, [], $cookies);

        $response->assertStatus(200);
    }

    public function testLogoutUnnamed()
    {
        $url = TestsHelper::getRoutePath('logout');

        $response = $this->followingRedirects()->call('POST', $url);

        $response->assertStatus(200);
    }

    public function testFinalLogin()
    {
        $url = TestsHelper::getRoutePath('login.create');

        $user_input1 = TestsHelper::$shared_objects['auth']['user_inputs'][0][0];

        $response = $this->followingRedirects()->call('POST', $url, $user_input1);

        $response->assertStatus(200);

        TestsHelper::storeLaravelSession($response);
    }
}
