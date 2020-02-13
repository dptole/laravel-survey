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
    $response = $this->call(
      'GET',
      TestsHelper::getRoutePath('survey.create'),
      [],
      ['laravel_session' => TestsHelper::$laravel_session]
    );

    $response->assertStatus(200);
  }

  public function testCreateSurvey() {
    foreach(TestsHelper::$shared_objects['survey']['samples'] as $sa):
      list($survey1) = $sa;

      $response = $this->followingRedirects()->call(
        'POST',
        TestsHelper::getRoutePath('survey.store'),
        $survey1,
        ['laravel_session' => TestsHelper::$laravel_session]
      );

      $response->assertStatus(200);
    endforeach;
  }

  public function testCreatedSurvey() {
    foreach(TestsHelper::$shared_objects['survey']['samples'] as $sa):
      list($survey1) = $sa;

      $surveys = Surveys::all();
      $this->assertCount(1, $surveys);

      $survey_db = $surveys[0];

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
}
