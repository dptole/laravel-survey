<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Webpatser\Uuid\Uuid;
use Carbon\Carbon;
use Tests\TestsHelper;
use App\Surveys;
use App\User;

class SurveyTest extends TestCase
{
  public function testShowCreateSurveyPage() {
    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.create'),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testCreateSurvey() {
    foreach(TestsHelper::$shared_objects['survey']['samples'] as $samples):
      foreach($samples as $survey1):
        $response = $this->followingRedirects()->call(
          'POST',
          TestsHelper::getRoutePath('survey.store'),
          $survey1,
          ['laravel_session' => TestsHelper::$laravel_session]
        );

        $response->assertStatus(200);
      endforeach;
    endforeach;
  }

  public function testCreatedSurvey() {
    $surveys_db = Surveys::get()->all();
    $this->assertCount(5, $surveys_db);

    list($samples) = TestsHelper::$shared_objects['survey']['samples'];

    foreach($samples as $survey1):
      $surveys_db = Surveys::where('name', '=', $survey1['name'])->get();
      $this->assertCount(1, $surveys_db);

      $survey_db = $surveys_db[0];

      TestsHelper::$shared_objects['survey']['samples_db'] []= $survey_db;

      $this->assertEquals($survey1['name'], $survey_db->name);
      $this->assertEquals($survey1['description'], $survey_db->description);
      $this->assertEquals($survey1['status'], $survey_db->status);
      $this->assertEquals(TestsHelper::$shared_objects['auth']['logged_in']->id, $survey_db->user_id);
      $this->assertTrue(Uuid::validate($survey_db->uuid));
      $this->assertInstanceOf(Carbon::class, $survey_db->created_at);
      $this->assertInstanceOf(Carbon::class, $survey_db->updated_at);
      $this->assertEquals($survey_db->updated_at . '', $survey_db->created_at . '');
    endforeach;
  }

  public function testCreatedSurveyHtml() {
    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('dashboard'),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $dom = new \DOMDocument;
    @$dom->loadHtml($response->content());

    $tbodies = $dom->getElementsByTagName('tbody');
    $this->assertEquals(1, $tbodies->length);
    $tbody = $tbodies->item(0);
    $this->assertEquals(5, $tbody->getElementsByTagName('tr')->length);

    $response->assertStatus(200);
  }

  public function testDeleteSurvey() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.destroy', [$sample_db->uuid]),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testDeletedSurvey() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

    $surveys_db = Surveys::where('id', $sample_db->id)->get();
    $this->assertCount(0, $surveys_db);
  }

  public function testDeletedSurveyHtml() {
    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('dashboard'),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $dom = new \DOMDocument;
    @$dom->loadHtml($response->content());

    $tbodies = $dom->getElementsByTagName('tbody');
    $this->assertEquals(1, $tbodies->length);
    $tbody = $tbodies->item(0);
    $this->assertEquals(4, $tbody->getElementsByTagName('tr')->length);

    $response->assertStatus(200);
  }

  public function testDeleteInvalidSurvey() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.destroy', [$sample_db->uuid]),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testSurveyStatsBeforeRun() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.stats', [$sample_db->uuid]),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testSurveyEdit() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.edit', [$sample_db->uuid]),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testSurveyEditInvalid() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.edit', [$sample_db->uuid]),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testSurveyRunInvalid() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.run', [$sample_db->uuid]),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testSurveyRunZeroQuestions() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.run', [$sample_db->uuid]),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testSurveyPauseInvalid() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.pause', [$sample_db->uuid]),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testSurveyPauseZeroQuestionsInvalidStatus() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

    $response = $this->followingRedirects()->call(
      'GET',
      TestsHelper::getRoutePath('survey.pause', [$sample_db->uuid]),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testSurveyUpdateInvalid() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][0];

    $response = $this->followingRedirects()->call(
      'POST',
      TestsHelper::getRoutePath('survey.update', [$sample_db->uuid]),
      TestsHelper::$shared_objects['survey']['samples'][0][0],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testSurveyUpdate() {
    $sample_db = TestsHelper::$shared_objects['survey']['samples_db'][1];

    $response = $this->followingRedirects()->call(
      'POST',
      TestsHelper::getRoutePath('survey.update', [$sample_db->uuid]),
      TestsHelper::$shared_objects['survey']['samples'][0][1],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }
}
