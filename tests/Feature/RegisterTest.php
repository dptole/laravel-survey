<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Webpatser\Uuid\Uuid;
use Carbon\Carbon;
use App\Helper;
use App\User;

class RegisterTest extends TestCase
{
  public function testRegister()
  {
    $user_input = [
      'name' => 'test@user.com',
      'email' => 'test@user.com',
      'password' => 'test@user.com',
      'password_confirmation' => 'test@user.com'
    ];

    $response = $this->post(
      Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') . '/register',
      $user_input,
      [
        'content-type' => 'application/x-www-form-urlencoded'
      ]
    );

    $response->assertStatus(302);

    return $user_input;
  }

  /** @depends testRegister */
  public function testUserRegistered($user_input) {
    $users = User::where('email', '=', $user_input['email'])->limit(1)->get();
    $this->assertCount(1, $users);

    $this->assertIsNumeric($users[0]['id']);
    $this->assertEquals($user_input['name'], $users[0]['name']);
    $this->assertEquals($user_input['email'], $users[0]['email']);
    $this->assertTrue(password_verify($user_input['password'], $users[0]['password']));
    $this->assertTrue(Uuid::validate($users[0]['uuid']));
    $this->assertNull($users[0]['remember_token']);
    $this->assertInstanceOf(Carbon::class, $users[0]['created_at']);
    $this->assertInstanceOf(Carbon::class, $users[0]['updated_at']);
    $this->assertEquals($users[0]['updated_at'] . '', $users[0]['created_at'] . '');
  }
}
