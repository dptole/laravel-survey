<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestResponse;

use App\Helper;

class TestsHelper
{
  static $laravel_session = null;

  static $shared_objects = [
    'auth' => [
      'user_inputs' => [
        [
          [
            'name' => 'test@user.com',
            'email' => 'test@user.com',
            'password' => 'test@user.com',
            'password_confirmation' => 'test@user.com'
          ],

          [
            'name' => 'test2@user.com',
            'email' => 'test2@user.com',
            'password' => 'test2@user.com',
            'password_confirmation' => 'test2@user.com'
          ],

          [
            'name' => 'recaptcha@user.com',
            'email' => 'recaptcha@user.com',
            'password' => 'recaptcha@user.com',
            'password_confirmation' => 'recaptcha@user.com'
          ],

          [
            'name' => 'direct@user.com',
            'email' => 'direct@user.com',
            'password' => 'direct@user.com'
          ]
        ]
      ],
      'logged_in' => null
    ],

    'survey' => [
      'samples' => [
        [
          [
            'name' => 'survey1 name',
            'description' => 'survey1 description',
            'status' => 'draft'
          ]
        ]
      ]
    ]
  ];

  public static function getLaravelSessionCookieName() {
    return app('session')->getSessionConfig()['cookie'];
  }

  public static function getLaravelSession(TestResponse $response) {
    foreach($response->headers->getCookies() as $cookie):
      if(self::getLaravelSessionCookieName() !== $cookie->getName())
        continue;

      return $cookie->getValue();
    endforeach;
  }

  public static function storeLaravelSession(TestResponse $response) {
    $laravel_session = self::getLaravelSession($response);

    if(!$laravel_session)
      return false;

    self::$laravel_session = $laravel_session;

    return true;
  }

  public static function getRoutePath($route, array $route_arguments = []) {
    return Helper::urlRemoveDomain(
      route($route, $route_arguments)
    );
  }
}
