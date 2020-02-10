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
      $user_input
    );

    $response->assertStatus(302);

    return $user_input;
  }

  /** @depends testRegister */
  public function testUserRegistered($user_input) {
    $users = User::where('email', '=', $user_input['email'])->limit(1)->get();
    $this->assertCount(1, $users);
    $user = $users[0];

    $this->assertIsNumeric($user['id']);
    $this->assertEquals($user_input['name'], $user['name']);
    $this->assertEquals($user_input['email'], $user['email']);
    $this->assertTrue(password_verify($user_input['password'], $user['password']));
    $this->assertTrue(Uuid::validate($user['uuid']));
    $this->assertNull($user['remember_token']);
    $this->assertInstanceOf(Carbon::class, $user['created_at']);
    $this->assertInstanceOf(Carbon::class, $user['updated_at']);
    $this->assertEquals($user['updated_at'] . '', $user['created_at'] . '');
  }
}
