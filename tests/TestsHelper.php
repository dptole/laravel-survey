<?php

namespace Tests;

use App\Helper;
use Illuminate\Foundation\Testing\TestResponse;

class TestsHelper
{
    public static $laravel_session = null;

    public static $shared_objects = [
        'auth' => [
            'user_inputs' => [
                [
                    [
                        'name'                  => 'test@user.com',
                        'email'                 => 'test@user.com',
                        'password'              => 'test@user.com',
                        'password_confirmation' => 'test@user.com',
                    ],

                    [
                        'name'                  => 'test2@user.com',
                        'email'                 => 'test2@user.com',
                        'password'              => 'test2@user.com',
                        'password_confirmation' => 'test2@user.com',
                    ],

                    [
                        'name'                  => 'recaptcha@user.com',
                        'email'                 => 'recaptcha@user.com',
                        'password'              => 'recaptcha@user.com',
                        'password_confirmation' => 'recaptcha@user.com',
                    ],

                    [
                        'name'     => 'direct@user.com',
                        'email'    => 'direct@user.com',
                        'password' => 'direct@user.com',
                    ],
                ],
            ],
            'logged_in' => null,
        ],

        'survey' => [
            'samples' => [
                [
                    [
                        'name'        => 'survey name 1',
                        'description' => 'survey description 1',
                        'status'      => 'draft',
                    ],
                    [
                        'name'        => 'survey name 2',
                        'description' => 'survey description 2',
                        'status'      => 'draft',
                    ],
                    [
                        'name'        => 'survey name 3',
                        'description' => 'survey description 3',
                        'status'      => 'draft',
                    ],
                    [
                        'name'        => 'survey name 4',
                        'description' => 'survey description 4',
                        'status'      => 'draft',
                    ],
                    [
                        'name'        => 'survey name 5',
                        'description' => 'survey description 5',
                        'status'      => 'draft',
                    ],
                ],
            ],
            'samples_db' => [],
        ],
    ];

    public static function getSessionCookies() {
        return [
            'laravel_session' => self::$laravel_session
        ];
    }

    public static function getLaravelSessionCookieName()
    {
        return app('session')->getSessionConfig()['cookie'];
    }

    public static function getLaravelSession(TestResponse $response)
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if (self::getLaravelSessionCookieName() !== $cookie->getName()) {
                continue;
            }

            return $cookie->getValue();
        }
    }

    public static function storeLaravelSession(TestResponse $response)
    {
        $laravel_session = self::getLaravelSession($response);

        if (!$laravel_session) {
            return false;
        }

        self::$laravel_session = $laravel_session;

        return true;
    }

    public static function getRoutePath($route, array $route_arguments = [])
    {
        return Helper::urlRemoveDomain(
      route($route, $route_arguments)
    );
    }
}
