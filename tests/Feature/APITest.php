<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\TestsHelper;
use Webpatser\Uuid\Uuid;

class APITest extends TestCase
{
    public function testGetSessionIdInvalidSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('api.get_session_id', [$survey_db->uuid]);

        $response = $this->followingRedirects()->call('POST', $url, []);

        $response->assertStatus(400);

        $json_string = $response->content();

        $this->assertJson($json_string);

        $json = json_decode($json_string);

        $this->assertIsObject($json);
        $this->assertObjectHasAttribute('error', $json);
        $this->assertIsObject($json->error);
        $this->assertObjectHasAttribute('code', $json->error);
        $this->assertObjectHasAttribute('detail', $json->error);
        $this->assertObjectHasAttribute('error', $json->error);
        $this->assertObjectHasAttribute('error_data', $json->error);
        $this->assertObjectHasAttribute('real_error', $json->error);
        $this->assertObjectHasAttribute('status', $json->error);

        $this->assertIsNumeric($json->error->code);
        $this->assertEquals('The survey uuid given is invalid.', $json->error->detail);
        $this->assertEquals($json->error->real_error, $json->error->error);
        $this->assertTrue(Uuid::validate($json->error->error_data));
        $this->assertEquals(400, $json->error->status);
    }

    public function testGetSessionIdSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('api.get_session_id', [$survey_db->uuid]);

        $response = $this->followingRedirects()->call('POST', $url, []);

        $response->assertStatus(200);
    }

    public function testSaveSurvey()
    {
        $this->markTestIncomplete();
    }

    public function testFetchCountryInfoSessionUuid()
    {
        $this->markTestIncomplete();
    }

    public function testFetchCountryInfoSessionId()
    {
        $this->markTestIncomplete();
    }

    public function testFetchCountryInvalidSession()
    {
        $this->markTestIncomplete();
    }
}
