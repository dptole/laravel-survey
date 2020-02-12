<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Webpatser\Uuid\Uuid;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestsHelper;
use App\Helper;
use App\User;

class AuthTest extends TestCase
{
  public function getUserInputsProvider() {
    $user_input1 = [
      'name' => 'test@user.com',
      'email' => 'test@user.com',
      'password' => 'test@user.com',
      'password_confirmation' => 'test@user.com'
    ];

    $user_input2 = [
      'name' => 'test2@user.com',
      'email' => 'test2@user.com',
      'password' => 'test2@user.com',
      'password_confirmation' => 'test2@user.com'
    ];

    $user_recaptcha = [
      'name' => 'recaptcha@user.com',
      'email' => 'recaptcha@user.com',
      'password' => 'recaptcha@user.com',
      'password_confirmation' => 'recaptcha@user.com'
    ];

    return [
      [$user_input1, $user_input2, $user_recaptcha]
    ];
  }

  /** @dataProvider getUserInputsProvider */
  public function testRegister($user_input1, $user_input2, $user_recaptcha) {
    $response1 = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/register',
      $user_input1
    );

    $response1->assertStatus(302);

    // Following redirects
    $response2 = $this->followingRedirects()->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/register',
      $user_input2
    );

    $response2->assertStatus(200);
  }

  /** @dataProvider getUserInputsProvider */
  public function testUserAfterRegistered($user_input, $user_input2, $user_recaptcha) {
    $users = User::where('email', '=', $user_input['email'])->limit(1)->get();
    $this->assertCount(1, $users);
    $user_db = $users[0];

    $this->assertIsNumeric($user_db['id']);
    $this->assertEquals($user_input['name'], $user_db['name']);
    $this->assertEquals($user_input['email'], $user_db['email']);
    $this->assertTrue(password_verify($user_input['password'], $user_db['password']));
    $this->assertTrue(Uuid::validate($user_db['uuid']));
    $this->assertNull($user_db['remember_token']);
    $this->assertInstanceOf(Carbon::class, $user_db['created_at']);
    $this->assertInstanceOf(Carbon::class, $user_db['updated_at']);
    $this->assertEquals($user_db['updated_at'] . '', $user_db['created_at'] . '');
  }

  /** @dataProvider getUserInputsProvider */
  public function testRegisterWithGoogleReCaptchaEnabled($user_input1, $user_input2, $user_recaptcha) {
    $GLOBALS['isGoogleReCaptchaEnabled'] = true;

    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/register',
      $user_recaptcha
    );

    $response->assertStatus(302);

    unset($GLOBALS['isGoogleReCaptchaEnabled']);
  }

  /** @dataProvider getUserInputsProvider */
  public function testNotRegisteredWithGoogleReCaptchaEnabled($user_input1, $user_input2, $user_recaptcha) {
    $users = User::where('email', '=', $user_recaptcha['email'])->limit(1)->get();
    $this->assertCount(0, $users);
  }

  /** @dataProvider getUserInputsProvider */
  public function testLogin($user_input1, $user_input2, $user_recaptcha) {
    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/login',
      $user_input1
    );

    TestsHelper::storeLaravelSession($response);

    $response->assertStatus(302);

    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/login',
      $user_input2
    );

    $response->assertStatus(302);
  }

  /** @dataProvider getUserInputsProvider */
  public function testLoginWithGoogleReCaptchaFailed($user_input1, $user_input2, $user_recaptcha) {
    $GLOBALS['isGoogleReCaptchaEnabled'] = true;

    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/login',
      $user_input1
    );

    $response->assertStatus(302);

    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/login',
      $user_input2
    );

    $response->assertStatus(302);

    unset($GLOBALS['isGoogleReCaptchaEnabled']);
  }

  /** @dataProvider getUserInputsProvider */
  public function testLoginWithGoogleReCaptchaSucceeded($user_input1, $user_input2, $user_recaptcha) {
    $GLOBALS['isGoogleReCaptchaEnabled'] = true;
    $GLOBALS['googleReCaptchaFailed'] = false;

    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/login',
      $user_input1
    );

    $response->assertStatus(302);

    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/login',
      $user_input2
    );

    $response->assertStatus(302);

    unset($GLOBALS['isGoogleReCaptchaEnabled']);
    unset($GLOBALS['googleReCaptchaFailed']);
  }

  /** @dataProvider getUserInputsProvider */
  public function testLogout($user_input1, $user_input2, $user_recaptcha) {
    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/logout',
      $user_input1
    );

    $response->assertStatus(302);
  }

  public function testLogoutUnnamed() {
    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/logout'
    );

    $response->assertStatus(302);
  }
}
