<?php

namespace Tests;

use App\AnswersSessions;
use App\Helper;
use Illuminate\Foundation\Testing\TestResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                        'g-recaptcha-response'  => '.',
                    ],

                    [
                        'name'     => 'direct@user.com',
                        'email'    => 'direct@user.com',
                        'password' => 'direct@user.com',
                    ],

                    [
                        'name'     => 'invalid-email',
                        'email'    => 'invalid-email',
                        'password' => 'invalid-email',
                    ],
                ],
            ],
            'logged_in' => null,
        ],

        'question' => [
            'samples' => [
                'free' => [
                    'description'       => 'question description 1',
                    'questions_options' => [
                        [
                            'type'  => 'free',
                            'value' => '.',
                        ],
                    ],
                ],
                'check' => [
                    'description'       => 'question description 2',
                    'questions_options' => [
                        [
                            'type'  => 'check',
                            'value' => 'answer 1',
                        ],
                        [
                            'type'  => 'check',
                            'value' => 'answer 2',
                        ],
                    ],
                ],
            ],
            'samples_db' => [],
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

        'answer_sessions' => [],
    ];

    public static function getSessionCookies()
    {
        return [
            'laravel_session' => self::$laravel_session,
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

    public static function storeAnswerSessions(TestResponse $response)
    {
        $json_string = $response->content();

        $json = json_decode($json_string);

        $answers_sessions = AnswersSessions::where('session_uuid', '=', $json->success->session_id)->get();

        self::$shared_objects['answer_sessions'][] = $answers_sessions[0];

        return true;
    }

    public static function getRoutePath($route, array $route_arguments = [])
    {
        $full_route = route($route, $route_arguments);
        return Helper::urlRemoveDomain($full_route);
    }

    static function getTestResponseContent(TestResponse $response)
    {
        if ($response->baseResponse instanceof BinaryFileResponse) {
            return "{$response->getFile()->openFile()}";
        }

        return $response->getContent();
    }
}
