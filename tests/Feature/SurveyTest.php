<?php

namespace Tests\Feature;

use App\Surveys;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\TestsHelper;
use Webpatser\Uuid\Uuid;

class SurveyTest extends TestCase
{
    public function testShowCreateSurveyPage()
    {
        $url = TestsHelper::getRoutePath('survey.create');

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testCreateSurvey()
    {
        $url = TestsHelper::getRoutePath('survey.create');

        $cookies = TestsHelper::getSessionCookies();

        foreach (TestsHelper::$shared_objects['survey']['samples'] as $samples) {
            foreach ($samples as $survey) {
                $response = $this->followingRedirects()->call('POST', $url, $survey, $cookies);

                $response->assertStatus(200);
            }
        }
    }

    public function testCreatedSurvey()
    {
        $surveys_db = Surveys::get()->all();
        $this->assertCount(5, $surveys_db);

        list($samples) = TestsHelper::$shared_objects['survey']['samples'];

        foreach ($samples as $survey) {
            $surveys_db = Surveys::where('name', '=', $survey['name'])->get();
            $this->assertCount(1, $surveys_db);

            $survey_db = $surveys_db[0];

            TestsHelper::$shared_objects['survey']['samples_db'][] = $survey_db;

            $this->assertEquals($survey['name'], $survey_db->name);
            $this->assertEquals($survey['description'], $survey_db->description);
            $this->assertEquals($survey['status'], $survey_db->status);
            $this->assertEquals(TestsHelper::$shared_objects['auth']['logged_in']->id, $survey_db->user_id);
            $this->assertTrue(Uuid::validate($survey_db->uuid));
            $this->assertInstanceOf(Carbon::class, $survey_db->created_at);
            $this->assertInstanceOf(Carbon::class, $survey_db->updated_at);
            $this->assertEquals($survey_db->updated_at.'', $survey_db->created_at.'');
        }
    }

    public function testCreatedSurveyHtml()
    {
        $url = TestsHelper::getRoutePath('dashboard');

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $dom = new \DOMDocument();
        @$dom->loadHtml($response->content());

        $tbodies = $dom->getElementsByTagName('tbody');
        $this->assertEquals(1, $tbodies->length);
        $tbody = $tbodies->item(0);
        $this->assertEquals(5, $tbody->getElementsByTagName('tr')->length);

        $response->assertStatus(200);
    }

    public function testDeleteSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('survey.destroy', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testDeletedSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $surveys_db = Surveys::where('id', $survey_db->id)->get();
        $this->assertCount(0, $surveys_db);
    }

    public function testDeletedSurveyHtml()
    {
        $url = TestsHelper::getRoutePath('dashboard');

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $dom = new \DOMDocument();
        @$dom->loadHtml($response->content());

        $tbodies = $dom->getElementsByTagName('tbody');
        $this->assertEquals(1, $tbodies->length);
        $tbody = $tbodies->item(0);
        $this->assertEquals(4, $tbody->getElementsByTagName('tr')->length);

        $response->assertStatus(200);
    }

    public function testDeleteInvalidSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('survey.destroy', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyStatsInvalid()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('survey.stats', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyStatsBeforeRun()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.stats', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyEdit()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.edit', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyEditInvalid()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('survey.edit', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyRunInvalid()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('survey.run', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyRunZeroQuestions()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.run', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyPauseInvalid()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('survey.pause', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyPauseZeroQuestionsInvalidStatus()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.pause', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyUpdateInvalid()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('survey.update', [$survey_db->uuid]);

        $data = TestsHelper::$shared_objects['survey']['samples'][0][0];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyUpdate()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.update', [$survey_db->uuid]);

        $data = TestsHelper::$shared_objects['survey']['samples'][0][1];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }
}
