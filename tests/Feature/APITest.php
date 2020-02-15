<?php

namespace Tests\Feature;

use App\Answers;
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

    public function testGetSessionIdSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('api.get_session_id', [$survey_db->uuid]);

        $response = $this->followingRedirects()->call('POST', $url, []);

        $json_string = $response->content();

        $this->assertJson($json_string);

        TestsHelper::storeAnswerSessions($response);

        $response->assertStatus(200);
    }

    public function testSaveFirstSurveyQuestion()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $answer_session = TestsHelper::$shared_objects['answer_sessions'][0];

        $question_options = QuestionsOptions::where('question_id', '=', $question_db->id)->get();

        $url = TestsHelper::getRoutePath('api.save_survey_answer');

        $data = [
            'survey_id'          => $survey_db->id,
            'question_id'        => $question_db->id,
            'question_option_id' => $question_options[0]->id,
            'free_text'          => '.',
            'answers_session_id' => $answer_session->session_uuid,
        ];

        $response = $this->followingRedirects()->call('POST', $url, $data);

        $response->assertStatus(200);
    }

    public function testFirstSavedSurveyQuestion()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $answer_session = TestsHelper::$shared_objects['answer_sessions'][0];

        $question_options = QuestionsOptions::where('question_id', '=', $question_db->id)->get();

        $answer_session = TestsHelper::$shared_objects['answer_sessions'][0];

        $answers = Answers::where('answers_session_id', '=', $answer_session->id)->get();

        $this->assertCount(1, $answers);
        $this->assertEquals($survey_db->id, $answers[0]->survey_id);
        $this->assertEquals($question_db->id, $answers[0]->question_id);
        $this->assertEquals($question_options[0]->id, $answers[0]->question_option_id);
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
