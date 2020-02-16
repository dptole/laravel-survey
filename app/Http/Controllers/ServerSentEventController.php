<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Sse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServerSentEventController extends Controller
{
    const CHUNK_SEPARATOR = "\n\n";
    const LINES_SEPARATOR = "\n";
    const KV_ASSIGNER = ': ';

    private function parsePayload($payload)
    {
        $parsed_payload = '';

        if (is_string($payload)) {
            $parsed_payload = $payload;
        } elseif (is_array($payload) && is_string($payload['data'])) {
            $lines = [];

            foreach ($payload as $key => $value) {
                $lines[] = $key.self::KV_ASSIGNER.$value;
            }

            $parsed_payload = implode(self::LINES_SEPARATOR, $lines);
        }

        return $parsed_payload.self::CHUNK_SEPARATOR;
    }

    private function sendIdleEvent($id)
    {
        echo $this->parsePayload([
            'id'    => $id,
            'event' => 'idle',
            'data'  => '{}',
        ]);
    }

    private function sendPayload($payload)
    {
        echo $this->parsePayload($payload);
    }

    private function mainLoop(Request $request, Closure $main_loop, $timeout_seconds = 2)
    {
        // https://chrisblackwell.me/server-sent-events-using-laravel-vue/
        $response = new StreamedResponse(function () use ($request, $main_loop, $timeout_seconds) {
            $id = 0;

            while (1) {
                // @codeCoverageIgnoreStart
                if (connection_aborted()) {
                    break;
                }
                // @codeCoverageIgnoreEnd

                $payload = $main_loop($id);

                if ($payload === null) {
                    $this->sendIdleEvent($id);
                } elseif ($payload === false) {
                    break;
                } else {
                    $this->sendPayload($payload);
                }

                if (Helper::isTestingEnv()) {
                    // You can't put this line of code after ob_get_level()
                    // because then PHPUnit starts running it calls ob_start()
                    // and that messes up the expectations
                    flush();
                    break;
                }

                // @codeCoverageIgnoreStart
                if (ob_get_level() > 0) {
                    ob_flush();
                    ob_end_flush();
                }

                flush();

                sleep($timeout_seconds);
                $id++;
                // @codeCoverageIgnoreEnd
            }
        });

        $response->headers->set('content-type', 'text/event-stream');
        // https://serverfault.com/questions/937665/does-nginx-show-x-accel-headers-in-response
        $response->headers->set('x-accel-buffering', 'no');
        $response->headers->set('cache-control', 'no-cache');

        return $response;
    }

    public function channel(Request $request, $channel)
    {
        $mocked_last_event = Helper::getTestEnvMockVar('Sse::last_event', null);

        $listener = Sse::listen($channel, $mocked_last_event);

        return $this->mainLoop($request, function ($id) use ($listener) {
            $mocked_content = Helper::getTestEnvMockVar('Sse::mocked_content', null);

            if ($mocked_content !== null) {
                return $mocked_content;
            }

            $event = $listener();

            if (!$event) {
                return null;
            }

            return [
                'id'    => $id,
                'event' => $event->event,
                'data'  => $event->message,
            ];
        });
    }
}
