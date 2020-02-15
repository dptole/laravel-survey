<?php

namespace Tests\Feature;

use App\Answers;
use App\Questions;
use App\QuestionsOptions;
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

    public function testGetSessionIdSurveyMaxmindViaHeader()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('api.get_session_id', [$survey_db->uuid]);

        $data = [
            'battery' => [
                'success' => true,
                'result'  => [
                    'charging'        => true,
                    'dischargingTime' => 666,
                    'level'           => 6.66,
                ],
            ],
            'connection' => [
                'effectiveType' => '666g',
                'downlink'      => '666',
                'rtt'           => '666',
            ],
            'date' => [
                'timezone'    => 180,
                'date_string' => '1970-01-01',
                'time_string' => '00:00:00',
            ],
            'window' => [
                'width'  => 666,
                'height' => 666,
            ],
        ];

        $_SERVER['MM_HEADER_EN_COUNTRY_NAME'] = 'Brazil';

        $response = $this->followingRedirects()->call('POST', $url, $data);

        $json_string = $response->content();

        $this->assertJson($json_string);

        TestsHelper::storeAnswerSessions($response);

        $response->assertStatus(200);

        unset($_SERVER['MM_HEADER_EN_COUNTRY_NAME']);
    }

    public function testGetSessionIdSurveyMaxmindViaIp()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('api.get_session_id', [$survey_db->uuid]);

        $data = [
            'battery' => [
                'success' => true,
                'result'  => [
                    'charging'        => true,
                    'dischargingTime' => 666,
                    'level'           => 6.66,
                ],
            ],
            'connection' => [
                'effectiveType' => '666g',
                'downlink'      => '666',
                'rtt'           => '666',
            ],
            'date' => [
                'timezone'    => 180,
                'date_string' => '1970-01-01',
                'time_string' => '00:00:00',
            ],
            'window' => [
                'width'  => 666,
                'height' => 666,
            ],
        ];

        $_SERVER['MM_IP_EN_COUNTRY_NAME'] = 'Japan';

        $response = $this->followingRedirects()->call('POST', $url, $data);

        $json_string = $response->content();

        $this->assertJson($json_string);

        $response->assertStatus(200);

        unset($_SERVER['MM_IP_EN_COUNTRY_NAME']);
    }

    public function testGetSessionIdSurveyInvalidHeaders()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('api.get_session_id', [$survey_db->uuid]);

        $data = [
            'battery' => [
                'success' => true,
                'result'  => [
                    'charging'        => true,
                    'dischargingTime' => 666,
                    'level'           => 6.66,
                ],
            ],
            'connection' => [
                'effectiveType' => '666g',
                'downlink'      => '666',
                'rtt'           => '666',
            ],
            'date' => [
                'timezone'    => 180,
                'date_string' => '1970-01-01',
                'time_string' => '00:00:00',
            ],
            'window' => [
                'width'  => 666,
                'height' => 666,
            ],
        ];

        $response = $this->followingRedirects()->call('POST', $url, $data);

        $json_string = $response->content();

        $this->assertJson($json_string);

        $response->assertStatus(200);
    }

    public function testSaveSurveyQuestions()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $answer_session = TestsHelper::$shared_objects['answer_sessions'][0];

        $survey_id = TestsHelper::$shared_objects['question']['samples_db'][0]->survey_id;

        $question_order = TestsHelper::$shared_objects['question']['samples_db'][0]->order;

        $questions_last_version = Questions::where('survey_id', '=', $survey_id)->where('order', '=', $question_order)->where('version', '=', 4)->get();

        $this->assertCount(1, $questions_last_version);

        $question_last_version = $questions_last_version[0];

        TestsHelper::$shared_objects['question']['samples_db'][] = $question_last_version;

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][2];

        $questions_options_db = QuestionsOptions::where('question_id', '=', $question_db->id)->get();

        $this->assertCount(2, $questions_options_db);

        $url = TestsHelper::getRoutePath('api.save_survey_answer');

        $data = [
            'survey_id'          => $survey_db->id,
            'question_id'        => $question_db->id,
            'question_option_id' => $questions_options_db[0]->id,
            'free_text'          => '.',
            'answers_session_id' => $answer_session->session_uuid,
        ];

        $response = $this->followingRedirects()->call('POST', $url, $data);

        $json_string = $response->content();

        $this->assertJson($json_string);

        $response->assertStatus(200);
    }

    public function testAfterSaveSurveyQuestions()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $answer_session = TestsHelper::$shared_objects['answer_sessions'][0];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][2];

        $answers = Answers::where('survey_id', '=', $survey_db->id)->where('question_id', '=', $question_db->id)->get();

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][2];

        $questions_options_db = QuestionsOptions::where('question_id', '=', $question_db->id)->get();

        $this->assertCount(2, $questions_options_db);
        $this->assertCount(1, $answers);
        $this->assertEquals($questions_options_db[0]->id, $answers[0]->question_option_id);
        $this->assertEquals($answer_session->id, $answers[0]->answers_session_id);
    }

    public function testFetchCountryInfoSessionUuid()
    {
        $answer_session = TestsHelper::$shared_objects['answer_sessions'][0];

        $url = TestsHelper::getRoutePath('api.fetch_country_info');

        $rand1 = rand(0, 255);

        $rand2 = rand(0, 255);

        $ip = '187.183.'.$rand1.'.'.$rand2;

        $data = [
            'answers_session_uuid' => $answer_session->session_uuid,
            'ip'                   => $ip,
        ];

        $response = $this->followingRedirects()->call('POST', $url, $data);

        $json_string = $response->content();

        $this->assertJson($json_string);

        $response->assertStatus(200);
    }

    public function testFetchCountryInfoSessionId()
    {
        $answer_session = TestsHelper::$shared_objects['answer_sessions'][0];

        $url = TestsHelper::getRoutePath('api.fetch_country_info');

        $rand1 = rand(0, 255);

        $rand2 = rand(0, 255);

        $ip = '187.183.'.$rand1.'.'.$rand2;

        $data = [
            'answers_session_id' => $answer_session->id,
            'ip'                 => $ip,
        ];

        $response = $this->followingRedirects()->call('POST', $url, $data);

        $json_string = $response->content();

        $this->assertJson($json_string);

        $response->assertStatus(200);
    }

    public function testFetchCountryInvalidSession()
    {
        $url = TestsHelper::getRoutePath('api.fetch_country_info');

        $data = [
            'answers_session_id' => 0,
            'ip'                 => '.',
        ];

        $response = $this->followingRedirects()->call('POST', $url, $data);

        $json_string = $response->content();

        $this->assertJson($json_string);

        $response->assertStatus(400);
    }
}
