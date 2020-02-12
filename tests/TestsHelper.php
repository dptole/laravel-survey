<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestResponse;

class TestsHelper
{
  static $laravel_session = null;

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
}
