<?php

namespace Tests\Feature;

use App\Questions;
use App\QuestionsOptions;
use Tests\TestCase;
use Tests\TestsHelper;

class QuestionTest extends TestCase
{
    public function testShowCreateQuestionPage()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.create', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testShowInvalidCreateQuestionPage()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.create', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testCreateQuestionForInvalidSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.store', [$survey_db->uuid]);

        $data = TestsHelper::$shared_objects['question']['samples']['free'];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testCreateFreeQuestion()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.store', [$survey_db->uuid]);

        $data = TestsHelper::$shared_objects['question']['samples']['free'];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testCreatedFreeQuestion()
    {
        $data = TestsHelper::$shared_objects['question']['samples']['free'];

        $questions = Questions::where('description', '=', $data['description'])->get();

        $this->assertCount(1, $questions);

        $question = $questions[0];

        $questions_options = QuestionsOptions::where('question_id', '=', $question->id)->limit(1)->get();

        $this->assertCount(1, $questions_options);

        $question_option = $questions_options[0];

        $this->assertEquals($data['questions_options'][0]['type'], $question_option->type);

        $this->assertEquals($data['description'], $question->description);

        TestsHelper::$shared_objects['question']['samples_db'][] = $question;
    }

    public function testUpdateQuestion()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.update', [$survey_db->uuid, $question_db->uuid]);

        $data = TestsHelper::$shared_objects['question']['samples']['free'];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testShowEditQuestionPage()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.edit', [$survey_db->uuid, $question_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testCreateCheckQuestion()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.store', [$survey_db->uuid]);

        $data = TestsHelper::$shared_objects['question']['samples']['check'];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testCreatedCheckQuestion()
    {
        $data = TestsHelper::$shared_objects['question']['samples']['check'];

        $questions = Questions::where('description', '=', $data['description'])->get();

        $this->assertCount(1, $questions);

        $question = $questions[0];

        $questions_options = QuestionsOptions::where('question_id', '=', $question->id)->limit(2)->get();

        $this->assertCount(2, $questions_options);

        $question_option1 = $questions_options[0];
        $question_option2 = $questions_options[1];

        $this->assertEquals($data['questions_options'][0]['type'], $question_option1->type);
        $this->assertEquals($data['questions_options'][0]['value'], $question_option1->description);

        $this->assertEquals($data['questions_options'][1]['type'], $question_option2->type);
        $this->assertEquals($data['questions_options'][1]['value'], $question_option2->description);

        $this->assertEquals($data['description'], $question->description);

        TestsHelper::$shared_objects['question']['samples_db'][] = $question;
    }

    public function testShowChangeOrderPage()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.show_change_order', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testShowChangeOrderPageInvalidSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.show_change_order', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testStoreChangeOrderInvalidSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.store_change_order', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testStoreChangeOrder()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];
        $question_free_db = TestsHelper::$shared_objects['question']['samples_db'][0];
        $question_check_db = TestsHelper::$shared_objects['question']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.store_change_order', [$survey_db->uuid]);

        $data = [
            'questions' => [
                [
                    'id' => $question_check_db->id,
                ],
                [
                    'id' => $question_free_db->id,
                ],
            ],
        ];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testDeleteQuestion()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.delete', [$survey_db->uuid, $question_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testDeleteInvalidQuestion()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.delete', [$survey_db->uuid, 'invalid']);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testShowEditQuestionPageAfterDeleted()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.edit', [$survey_db->uuid, $question_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testShowEditQuestionPageInvalidQuestion()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.edit', [$survey_db->uuid, 'invalid']);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testShowEditQuestionPageInvalidSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.edit', [$survey_db->uuid, $question_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testUpdateInvalidQuestion()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.update', [$survey_db->uuid, 'invalid']);

        $data = TestsHelper::$shared_objects['question']['samples']['free'];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyRun()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.run', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyRunTwice()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.run', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testStoreChangeOrderWhenRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.store_change_order', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testShowChangeOrderPageWhenRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.show_change_order', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testShowEditQuestionPageWhenRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.edit', [$survey_db->uuid, $question_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyStatsAfterRun()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.stats', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyPause()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.pause', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyPauseTwice()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.pause', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyKeepRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.run', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyUpdateWhenRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.update', [$survey_db->uuid]);

        $data = TestsHelper::$shared_objects['survey']['samples'][0][1];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testDeleteSurveyWhenRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.destroy', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testCreateQuestionWhenRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('question.store', [$survey_db->uuid]);

        $data = TestsHelper::$shared_objects['question']['samples']['free'];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testDeleteQuestionWhenRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.delete', [$survey_db->uuid, $question_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testUpdateQuestionWhenRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.update', [$survey_db->uuid, $question_db->uuid]);

        $data = TestsHelper::$shared_objects['question']['samples']['free'];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testUpdateQuestionForInvalidSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $question_db = TestsHelper::$shared_objects['question']['samples_db'][0];

        $url = TestsHelper::getRoutePath('question.update', [$survey_db->uuid, $question_db->uuid]);

        $data = TestsHelper::$shared_objects['question']['samples']['free'];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('POST', $url, $data, $cookies);

        $response->assertStatus(200);
    }

    public function testSurveyPauseForcingAlreadyPaused()
    {
        $GLOBALS['Surveys::ERR_PAUSE_SURVEY_ALREADY_PAUSED'] = true;

        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.pause', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);

        unset($GLOBALS['Surveys::ERR_PAUSE_SURVEY_ALREADY_PAUSED']);
    }

    public function testSurveyRunForcingSurveyNotFound()
    {
        $GLOBALS['Surveys::ERR_RUN_SURVEY_NOT_FOUND'] = true;

        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.run', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);

        unset($GLOBALS['Surveys::ERR_RUN_SURVEY_NOT_FOUND']);
    }

    public function testSurveyRunForcingSurveyInvalidStatus()
    {
        $GLOBALS['Surveys::ERR_RUN_SURVEY_INVALID_STATUS'] = true;

        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.run', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);

        unset($GLOBALS['Surveys::ERR_RUN_SURVEY_INVALID_STATUS']);
    }

    public function testSurveyRunForcingSurveyAlreadyRunning()
    {
        $GLOBALS['Surveys::ERR_RUN_SURVEY_NOT_FOUND'] = false;
        $GLOBALS['Surveys::ERR_RUN_SURVEY_INVALID_STATUS'] = false;
        $GLOBALS['Surveys::ERR_RUN_SURVEY_ALREADY_RUNNING'] = true;

        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('survey.run', [$survey_db->uuid]);

        $data = [];

        $cookies = TestsHelper::getSessionCookies();

        $response = $this->followingRedirects()->call('GET', $url, $data, $cookies);

        $response->assertStatus(200);

        unset($GLOBALS['Surveys::ERR_RUN_SURVEY_NOT_FOUND']);
        unset($GLOBALS['Surveys::ERR_RUN_SURVEY_INVALID_STATUS']);
        unset($GLOBALS['Surveys::ERR_RUN_SURVEY_ALREADY_RUNNING']);
    }
}
