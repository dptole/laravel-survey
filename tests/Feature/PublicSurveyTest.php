<?php

namespace Tests\Feature;

use App\Surveys;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\TestsHelper;

class PublicSurveyTest extends TestCase
{

    private function assertResponseMatchesSurvey(TestResponse $response, Surveys $survey_db)
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->content());

        $survey_dom = null;

        foreach($dom->getElementsByTagName('div') as $div):
          $class = $div->attributes->getNamedItem('class');

          if(!$class) continue;

          if(strpos($class->nodeValue, 'public-survey-content') !== false):
            $survey_dom = $div;
            break;
          endif;
        endforeach;

        $this->assertInstanceof(\DOMElement::class, $survey_dom);

        $data_survey = $survey_dom->attributes->getNamedItem('data-survey');

        $this->assertInstanceof(\DOMAttr::class, $data_survey);

        $this->assertJson($data_survey->nodeValue);

        $json_data_survey = json_decode($data_survey->nodeValue);

        $this->assertEquals($survey_db->id, $json_data_survey->id);
        $this->assertEquals($survey_db->user_id, $json_data_survey->user_id);
        $this->assertEquals($survey_db->uuid, $json_data_survey->uuid);
        $this->assertEquals($survey_db->name, $json_data_survey->name);
        $this->assertEquals($survey_db->shareable_link, $json_data_survey->shareable_link);
        $this->assertNotEquals($survey_db->status, $json_data_survey->status);
        $this->assertNotEquals($survey_db->updated_at.'', $json_data_survey->updated_at);
        $this->assertEquals($survey_db->created_at.'', $json_data_survey->created_at);

        $this->assertIsArray($json_data_survey->all_questions);
        $this->assertNotEmpty($json_data_survey->all_questions);
    }

    public function testShowPublicInvalidSurvey()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('public_survey.show', [$survey_db->uuid]);

        $response = $this->followingRedirects()->call(
            'GET',
            $url
        );

        $response->assertStatus(200);
    }

    public function testShowPublicSurveyNotRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][2];

        $url = TestsHelper::getRoutePath('public_survey.show', [$survey_db->uuid]);

        $response = $this->followingRedirects()->call(
            'GET',
            $url
        );

        $response->assertStatus(200);
    }

    public function testShowPublicSurveyRunning()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('public_survey.show', [$survey_db->uuid]);

        $response = $this->followingRedirects()->call(
            'GET',
            $url
        );

        $response->assertStatus(200);

        $this->assertResponseMatchesSurvey($response, $survey_db);
    }

    public function testShowPublicSurveyRunningViaShareableLink()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('public_survey.shareable_link', [$survey_db->shareable_link]);

        $response = $this->followingRedirects()->call(
            'GET',
            $url
        );

        $response->assertStatus(200);

        $this->assertResponseMatchesSurvey($response, $survey_db);
    }

    public function testShowPublicSurveyRunningViaShareableLinkInvalid()
    {
        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

        $url = TestsHelper::getRoutePath('public_survey.shareable_link', [$survey_db->shareable_link]);

        $response = $this->followingRedirects()->call(
            'GET',
            $url
        );

        $response->assertStatus(200);
    }

    public function testShowPublicSurveyRunningZeroQuestions()
    {
        $GLOBALS['PublicSurvey::areThereQuestions'] = false;

        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('public_survey.show', [$survey_db->uuid]);

        $response = $this->followingRedirects()->call(
            'GET',
            $url
        );

        $response->assertStatus(200);

        unset($GLOBALS['PublicSurvey::areThereQuestions']);
    }

    public function testShowPublicSurveyRunningIncompleteAnswers()
    {
        $GLOBALS['PublicSurvey::properQuestion'] = false;

        $survey_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

        $url = TestsHelper::getRoutePath('public_survey.show', [$survey_db->uuid]);

        $response = $this->followingRedirects()->call(
            'GET',
            $url
        );

        $response->assertStatus(200);

        unset($GLOBALS['PublicSurvey::properQuestion']);
    }
}
