<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\TestsHelper;
use Webpatser\Uuid\Uuid;

class AuthTest extends TestCase
{
    public function testRegister()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct) = $ui;

            $response1 = $this->post(
        TestsHelper::getRoutePath('register.create'),
        $user_input1
      );

            $response1->assertStatus(302);

            $response2 = $this->followingRedirects()->post(
        TestsHelper::getRoutePath('register.create'),
        $user_input2
      );

            $response2->assertStatus(200);
        }
    }

    /** @depends testRegister */
    public function testUserAfterRegistered($_)
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
            list($user_input1, $user_input2, $user_recaptcha, $user_direct) = $ui;

            $GLOBALS['isGoogleReCaptchaEnabled'] = true;

            $response = $this->post(
        TestsHelper::getRoutePath('register.create'),
        $user_recaptcha
      );

            $response->assertStatus(302);

            unset($GLOBALS['isGoogleReCaptchaEnabled']);
        }
    }

    public function testNotRegisteredWithGoogleReCaptchaEnabled()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct) = $ui;

            $users = User::where('email', '=', $user_recaptcha['email'])->limit(1)->get();
            $this->assertCount(0, $users);
        }
    }

    public function testLogin()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct) = $ui;

            $response = $this->post(
        TestsHelper::getRoutePath('login.create'),
        $user_input1
      );

            $response->assertStatus(302);

            $response = $this->post(
        TestsHelper::getRoutePath('login.create'),
        $user_input2
      );

            $response->assertStatus(302);

            TestsHelper::storeLaravelSession($response);
        }
    }

    public function testLoginWithGoogleReCaptchaFailed()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct) = $ui;

            $GLOBALS['isGoogleReCaptchaEnabled'] = true;

            $response = $this->post(
        TestsHelper::getRoutePath('login.create'),
        $user_input1
      );

            $response->assertStatus(302);

            $response = $this->post(
        TestsHelper::getRoutePath('login.create'),
        $user_input2
      );

            $response->assertStatus(302);

            unset($GLOBALS['isGoogleReCaptchaEnabled']);
        }
    }

    public function testLoginWithGoogleReCaptchaSucceeded()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct) = $ui;

            $GLOBALS['isGoogleReCaptchaEnabled'] = true;
            $GLOBALS['googleReCaptchaFailed'] = false;

            $response = $this->post(
        TestsHelper::getRoutePath('login.create'),
        $user_input1
      );

            $response->assertStatus(302);

            $response = $this->post(
        TestsHelper::getRoutePath('login.create'),
        $user_input2
      );

            $response->assertStatus(302);

            unset($GLOBALS['isGoogleReCaptchaEnabled']);
            unset($GLOBALS['googleReCaptchaFailed']);
        }
    }

    public function testLogout()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct) = $ui;

            $response = $this->post(
        TestsHelper::getRoutePath('logout'),
        $user_input1
      );

            $response->assertStatus(302);

            $response = $this->post(
        TestsHelper::getRoutePath('logout'),
        $user_input2
      );

            $response->assertStatus(302);
        }
    }

    public function testLogoutUnnamed()
    {
        $response = $this->post(
      TestsHelper::getRoutePath('logout')
    );

        $response->assertStatus(302);
    }

    public function testRegisteringDirectly()
    {
        foreach (TestsHelper::$shared_objects['auth']['user_inputs'] as $ui) {
            list($user_input1, $user_input2, $user_recaptcha, $user_direct) = $ui;

            $generated_uuid = Uuid::generate(4).'';
            $encrypted_password = bcrypt($user_direct['password']);

            $user_db = new User();
            $user_db->name = $user_direct['name'];
            $user_db->uuid = $generated_uuid;
            $user_db->email = $user_direct['email'];
            $user_db->password = $encrypted_password;

            $user_db->save();

            $this->assertIsNumeric($user_db->id);
            $this->assertEquals($user_direct['name'], $user_db->name);
            $this->assertEquals($user_direct['email'], $user_db->email);
            $this->assertEquals($encrypted_password, $user_db->password);
            $this->assertTrue(password_verify($user_direct['password'], $user_db->password));
            $this->assertTrue(Uuid::validate($user_db->uuid));
            $this->assertEquals($generated_uuid, $user_db->uuid);
            $this->assertNull($user_db->remember_token);
            $this->assertInstanceOf(Carbon::class, $user_db->created_at);
            $this->assertInstanceOf(Carbon::class, $user_db->updated_at);
            $this->assertEquals($user_db->updated_at.'', $user_db->created_at.'');
        }
    }
}
