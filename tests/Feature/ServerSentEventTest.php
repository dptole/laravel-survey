<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\TestsHelper;

class ServerSentEventTest extends TestCase
{
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
