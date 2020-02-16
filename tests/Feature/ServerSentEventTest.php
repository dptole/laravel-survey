<?php

namespace Tests\Feature;

use App\Http\Controllers\ServerSentEventController;
use App\Sse;
use Tests\TestCase;
use Tests\TestsHelper;

class ServerSentEventTest extends TestCase
{
    public static function parseSseMessage(string $message = '')
    {
        $parsed_message = [];

        $message = trim($message);

        $split_message = explode(ServerSentEventController::LINES_SEPARATOR, $message);

        foreach ($split_message as $line) {
            list($k, $v) = explode(ServerSentEventController::KV_ASSIGNER, $line);
            $parsed_message[$k] = $v;
        }

        return $parsed_message;
    }

    public static function serializeSseMessage(array $message = [])
    {
        $serialized_message = [];

        foreach ($message as $key => $value) {
            $serialized_message[] = $key.ServerSentEventController::KV_ASSIGNER.$value;
        }

        return implode(ServerSentEventController::LINES_SEPARATOR, $serialized_message);
    }

    public function testSseNoContent()
    {
        $url = TestsHelper::getRoutePath('sse-channel', ['channel']);

        $response = $this->followingRedirects()->call('GET', $url);

        $response->assertStatus(200);

        $response->assertHeader('content-type', 'text/event-stream; charset=UTF-8');

        $response->assertHeader('x-accel-buffering', 'no');

        $content = $response->streamedContent();

        $parsed_sse_content = self::parseSseMessage($content);

        $this->assertEquals('idle', $parsed_sse_content['event']);

        $this->assertEquals('{}', $parsed_sse_content['data']);
    }

    public function testSseYesContent()
    {
        $GLOBALS['Sse::last_event'] = date('Y-m-d 00:00:00');

        $sse_message = [
            'message' => 'sse testing'
        ];

        Sse::trigger('channel', 'event', $sse_message);

        $url = TestsHelper::getRoutePath('sse-channel', ['channel']);

        $response = $this->followingRedirects()->call('GET', $url);

        $response->assertStatus(200);

        $response->assertHeader('content-type', 'text/event-stream; charset=UTF-8');

        $response->assertHeader('x-accel-buffering', 'no');

        // https://github.com/symfony/symfony/issues/25005#issuecomment-572565533
        $content = $response->streamedContent();

        $parsed_sse_content = self::parseSseMessage($content);

        $this->assertEquals('event', $parsed_sse_content['event']);

        $this->assertEquals(json_encode($sse_message), $parsed_sse_content['data']);

        unset($GLOBALS['Sse::last_event']);
    }

    public function testSseMockedContentString()
    {
        $GLOBALS['Sse::mocked_content'] = self::serializeSseMessage([
            'id'      => '0',
            'event'   => 'event',
            'message' => '{}'
        ]);

        $url = TestsHelper::getRoutePath('sse-channel', ['channel']);

        $response = $this->followingRedirects()->call('GET', $url);

        $response->assertStatus(200);

        $response->assertHeader('content-type', 'text/event-stream; charset=UTF-8');

        $response->assertHeader('x-accel-buffering', 'no');

        $content = $response->streamedContent();

        $this->assertEquals($GLOBALS['Sse::mocked_content'].ServerSentEventController::CHUNK_SEPARATOR, $content);

        unset($GLOBALS['Sse::mocked_content']);
    }

    public function testSseForceCloseConnection()
    {
        $GLOBALS['Sse::mocked_content'] = false;

        $url = TestsHelper::getRoutePath('sse-channel', ['channel']);

        $response = $this->followingRedirects()->call('GET', $url);

        $response->assertStatus(200);

        $response->assertHeader('content-type', 'text/event-stream; charset=UTF-8');

        $response->assertHeader('x-accel-buffering', 'no');

        $content = $response->streamedContent();

        $this->assertEquals('', trim($content));

        unset($GLOBALS['Sse::mocked_content']);
    }

    public function testSurveyStatsAfterAnswers()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.stats', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyStatsAfterAnswersForcingInvalidUserAgent()
    {
        $GLOBALS['validHeadersConfig'] = false;

        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.stats', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);

        unset($GLOBALS['validHeadersConfig']);
    }
}
