<?php

namespace Tests\Feature;

use App\ApiErrors;
use App\Helper;
use App\Questions;
use App\QuestionsOptions;
use App\Surveys;
use Tests\TestCase;
use Tests\TestsHelper;

class SetupTest extends TestCase
{
    public static $laravel_url_prefix = '';

    public function testShowMissingConfigs()
    {
        self::$laravel_url_prefix = Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL');

        Helper::updateDotEnvFileVars([
            'PUSHER_ENABLED'            => 'true',
            'GOOGLE_RECAPTCHA_ENABLED'  => 'true',
            'LARAVEL_SURVEY_PREFIX_URL' => 'testing',
        ]);

        $url = TestsHelper::getRoutePath('home');

        $response = $this->followingRedirects()->call('GET', $url);

        $response->assertStatus(200);

        $dom = new \DOMDocument();

        @$dom->loadHTML($response->content());

        $forms = $dom->getElementsByTagName('form');

        $this->assertInstanceOf(\DOMNodeList::class, $forms);

        $this->assertEquals(1, $forms->length);

        $attr_action = $forms->item(0)->attributes->getNamedItem('action');

        $this->assertInstanceOf(\DOMAttr::class, $attr_action);

        $this->assertEquals(route('setup-update-missing-configs'), $attr_action->nodeValue);
    }

    public function testShowMessageBlockingNavigationIfMissingConfigs()
    {
        $url = TestsHelper::getRoutePath('login');

        $response = $this->call('GET', $url);

        $response->assertStatus(302);

        $this->assertEquals(route('home'), $response->headers->get('Location'));
    }

    public function testUpdateMissingConfigsIncorrectly()
    {
        $url = TestsHelper::getRoutePath('setup-update-missing-configs');

        $data = [
            'PUSHER_ENABLED'     => 'true',
            'PUSHER_APP_ID'      => 'testing',
            'PUSHER_APP_KEY'     => 'testing',
            'PUSHER_APP_SECRET'  => 'testing',
            'PUSHER_APP_CLUSTER' => 'testing',

            'GOOGLE_RECAPTCHA_ENABLED'     => 'true',
            'GOOGLE_RECAPTCHA_SITE_SECRET' => 'testing',
            'GOOGLE_RECAPTCHA_SITE_KEY'    => 'testing',

            'LARAVEL_SURVEY_PREFIX_URL' => 'testing',
        ];

        $response = $this->call('POST', $url, $data);

        $response->assertStatus(302);

        $this->assertEquals(route('home'), $response->headers->get('Location'));
    }

    public function testUpdateMissingConfigs()
    {
        $url = TestsHelper::getRoutePath('setup-update-missing-configs');

        $data = [
            'PUSHER_ENABLED'     => 'false',
            'PUSHER_APP_ID'      => '',
            'PUSHER_APP_KEY'     => '',
            'PUSHER_APP_SECRET'  => '',
            'PUSHER_APP_CLUSTER' => '',

            'GOOGLE_RECAPTCHA_ENABLED'     => 'false',
            'GOOGLE_RECAPTCHA_SITE_SECRET' => '',
            'GOOGLE_RECAPTCHA_SITE_KEY'    => '',

            'LARAVEL_SURVEY_PREFIX_URL' => self::$laravel_url_prefix,
        ];

        $response = $this->call('POST', $url, $data);

        $response->assertStatus(302);

        $redirect_url = $response->headers->get('Location');

        $this->assertEquals(route('home'), $redirect_url);

        $response = $this->call('GET', $redirect_url);

        $dom = new \DOMDocument();

        @$dom->loadHTML($response->content());

        $xpath = new \DOMXPath($dom);

        $alerts = $xpath->query('//*[contains(@class, "alert-success")]');

        $this->assertInstanceOf(\DOMNodeList::class, $alerts);

        $this->assertEquals(1, $alerts->length);

        $alert = $alerts->item(0);

        $this->assertEquals('Success: Configurations updated successfully.', trim($alert->textContent));
    }

    public function testInvalidApiErrorType()
    {
        $this->assertFalse(ApiErrors::getErrorType('testing'));
    }

    public function testDeleteByOwnerInvalidQuestion()
    {
        $this->assertFalse(Questions::deleteByOwner('survey-uuid', 'question-uuid', 'user-id'));
    }

    public function testDeleteByOwnerAlreadyDeletedQuestion()
    {
        $questions = Questions::where('active', '=', 0)->limit(1)->get();

        $this->assertCount(1, $questions);

        $question = $questions[0];

        $surveys = Surveys::where('id', '=', $question->survey_id)->limit(1)->get();

        $this->assertCount(1, $surveys);

        $survey = $surveys[0];

        $survey_uuid = $survey->uuid;

        $question_uuid = $question->uuid;

        $user_id = $survey->user_id;

        $this->assertFalse(Questions::deleteByOwner($survey_uuid, $question_uuid, $user_id));
    }

    public function testDeleteByOwnerErrorUpdatingQuestion()
    {
        $GLOBALS['Questions::update_active_return'] = false;

        $surveys = Surveys::where([
            'status' => 'ready',
        ])->limit(1)->get();

        $this->assertCount(1, $surveys);

        $survey = $surveys[0];

        $questions = Questions::where([
            'active'    => 1,
            'survey_id' => $survey->id,
        ])->orderBy('version', 'DESC')->limit(1)->get();

        $this->assertCount(1, $questions);

        $question = $questions[0];

        $survey_uuid = $survey->uuid;

        $question_uuid = $question->uuid;

        $user_id = $survey->user_id;

        $this->assertFalse(Questions::deleteByOwner($survey_uuid, $question_uuid, $user_id));

        unset($GLOBALS['Questions::update_active_return']);
    }

    public function testUpdateOrderInvalidQuestion()
    {
        $this->assertFalse(Questions::updateOrder(-1, 1));
    }

    public function testGetZeroQuestionsOptions()
    {
        $questions_options = QuestionsOptions::getAllByQuestionId(-1);

        $this->assertIsArray($questions_options);

        $this->assertEmpty($questions_options);
    }

    public function testSaveArrayInvalidArguments()
    {
        $this->assertFalse(QuestionsOptions::saveArray([], 1));
    }

    public function testRunInvalidSurvey()
    {
        $this->assertEquals(Surveys::ERR_RUN_SURVEY_NOT_FOUND, Surveys::run('survey-uuid', 'user-id'));
    }

    public function testRunSurveyInvalidStatus()
    {
        $GLOBALS['Surveys::ERR_RUN_SURVEY_INVALID_STATUS'] = true;

        $surveys = Surveys::where('status', '=', 'draft')->limit(1)->get();

        $this->assertCount(1, $surveys);

        $survey = $surveys[0];

        $this->assertEquals(Surveys::ERR_RUN_SURVEY_INVALID_STATUS, Surveys::run($survey->uuid, $survey->user_id));

        unset($GLOBALS['Surveys::ERR_RUN_SURVEY_INVALID_STATUS']);
    }

    public function testPauseInvalidSurvey()
    {
        $this->assertEquals(Surveys::ERR_PAUSE_SURVEY_NOT_FOUND, Surveys::pause('survey-uuid', 'user-id'));
    }

    public function testPauseSurveyInvalidStatus()
    {
        $GLOBALS['Surveys::ERR_PAUSE_SURVEY_INVALID_STATUS'] = true;

        $surveys = Surveys::where('status', '=', 'ready')->limit(1)->get();

        $this->assertCount(1, $surveys);

        $survey = $surveys[0];

        $this->assertEquals(Surveys::ERR_PAUSE_SURVEY_INVALID_STATUS, Surveys::pause($survey->uuid, $survey->user_id));

        unset($GLOBALS['Surveys::ERR_PAUSE_SURVEY_INVALID_STATUS']);
    }
}
