<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Surveys;
use App\SurveysLastVersionsView;
use App\QuestionsOptions;
use App\QuestionsOptionsView;
use App\Questions;
use App\Answers;
use App\AnswersSessions;
use App\Helper;
use Webpatser\Uuid\Uuid;
use DB;

class Surveys extends Model {
  // https://laravel.com/docs/5.4/eloquent-mutators
  public function getUpdatedAtDiffAttribute() {
    return Helper::createCarbonDiffForHumans($this->updated_at);
  }

  public function getUpdatedAtRfc1123Attribute() {
    return Helper::createCarbonRfc1123String($this->updated_at);
  }

  public function getIsRunningAttribute() {
    return $this->status === 'ready';
  }

  public function getShareableUrlAttribute() {
    return env('LARAVEL_SURVEY_PREFIX_URL') . '/s/' . $this->shareable_link;
  }

  /************************************************/

  public static function getAllByOwner($user_id) {
    return Surveys::where('user_id', '=', $user_id)
      ->orderBy('updated_at', 'desc')
      ->paginate(5)
    ;
  }

  /************************************************/

  public static function getByOwner($uuid, $user_id) {
    return (
        $surveys = Surveys::where('user_id', '=', $user_id)
            ->where('uuid', '=', $uuid)
            ->limit(1)
            ->get()
        ) &&
          count($surveys) === 1
        ? $surveys[0]
        : null
    ;
  }

  /************************************************/

  public static function deleteByOwner($uuid, $user_id) {
    return Surveys::where([
      'user_id' => $user_id,
      'uuid' => $uuid
    ])->delete();
  }

  /************************************************/

  const ERR_RUN_SURVEY_OK = 0;
  const ERR_RUN_SURVEY_NOT_FOUND = 1;
  const ERR_RUN_SURVEY_INVALID_STATUS = 2;
  const ERR_RUN_SURVEY_ALREADY_RUNNING = 3;
  public static function run($uuid, $user_id) {
    $survey = Surveys::getByOwner($uuid, $user_id);

    if(!$survey):
      return Surveys::ERR_RUN_SURVEY_NOT_FOUND;
    elseif($survey->status !== 'draft'):
      return Surveys::ERR_RUN_SURVEY_INVALID_STATUS;
    elseif($survey->status === 'ready'):
      return Surveys::ERR_RUN_SURVEY_ALREADY_RUNNING;
    endif;

    $survey->status = 'ready';
    $survey->save();

    return Surveys::ERR_RUN_SURVEY_OK;
  }

  /************************************************/

  const ERR_PAUSE_SURVEY_OK = 0;
  const ERR_PAUSE_SURVEY_NOT_FOUND = 1;
  const ERR_PAUSE_SURVEY_INVALID_STATUS = 2;
  const ERR_PAUSE_SURVEY_ALREADY_PAUSED = 3;
  public static function pause($uuid, $user_id) {
    $survey = Surveys::getByOwner($uuid, $user_id);

    if(!$survey):
      return Surveys::ERR_PAUSE_SURVEY_NOT_FOUND;
    elseif($survey->status !== 'ready'):
      return Surveys::ERR_PAUSE_SURVEY_INVALID_STATUS;
    elseif($survey->status === 'draft'):
      return Surveys::ERR_PAUSE_SURVEY_ALREADY_PAUSED;
    endif;

    $survey->status = 'draft';
    $survey->save();

    return Surveys::ERR_PAUSE_SURVEY_OK;
  }

  /************************************************/

  public static function getAvailables() {
    return DB::table('surveys')
      ->select('surveys.*', 'users.name as author_name')
      ->join('users', 'users.id', '=', 'surveys.user_id')
      ->where('surveys.status', '=', 'ready')
      ->get()
    ;
  }

  /************************************************/

  const ERR_IS_RUNNING_SURVEY_OK = 0;
  const ERR_IS_RUNNING_SURVEY_NOT_FOUND = 1;
  const ERR_IS_RUNNING_SURVEY_NOT_RUNNING = 2;
  public static function isRunning($uuid) {
    $survey = Surveys::getByUuid($uuid);

    if(!$survey):
      return Surveys::ERR_IS_RUNNING_SURVEY_NOT_FOUND;
    endif;

    return $survey->status === 'ready'
      ? Surveys::ERR_IS_RUNNING_SURVEY_OK
      : Surveys::ERR_IS_RUNNING_SURVEY_NOT_RUNNING
    ;
  }

  /************************************************/

  public static function getByUuid($uuid) {
    return (
      $surveys = Surveys::where('uuid', '=', $uuid)
        ->limit(1)
        ->get()
      ) &&
        count($surveys) === 1
      ? $surveys[0]
      : null
    ;
  }

  /************************************************/

  public static function getIdByUuid($uuid, $user_id) {
    return ($survey = self::getByOwner($uuid, $user_id)) ? $survey->id : null;
  }

  /************************************************/

  public static function generateQuestionsNextVersion(Surveys $survey) {
    // If survey exists last_version will always exist
    $last_version = SurveysLastVersionsView::getById($survey->id);

    return array_map(function($question) use ($last_version) {
      return [
        'survey_id' => $question->survey_id,
        'order' => $question->order,
        'version' => $last_version->last_version + 1,
        'description' => $question->description,
        'uuid' => Uuid::generate(4)->string,
        'questions_options' => array_map(function($question_option) {
          return [
            'type' => $question_option->type,
            'value' => $question_option->description
          ];
        }, QuestionsOptions::getAllByQuestionId($question->id))
      ];
    }, Questions::getAllBySurveyIdUnpaginated($survey->id));
  }

  /************************************************/

  public static function getVersions(Surveys $survey) {
    return array_map(function($version) use ($survey) {
      return [
        'version' => $version,
        'answers_sessions' => AnswersSessions::getBySurveyId($survey->id, $version),
        'questions' => Questions::getAllByVersion($survey->id, $version)
      ];
    }, range(1, SurveysLastVersionsView::getById($survey->id)->last_version));
  }

  /************************************************/

  public static function getD3AnswersDataFromSurveyVersions($versions) {
    $survey_d3_data = array_map(function($version) {
      return [
        'fully_answered' => $version['fully_answered'],
        'version' => $version['version'],
        'not_fully_answered' => $version['not_fully_answered']
      ];
    }, $versions);

    $survey_d3_data = array_map(function($d3_data) {
      $d3_data['total'] = $d3_data['fully_answered'] + $d3_data['not_fully_answered'];
      return $d3_data;
    }, $survey_d3_data);

    usort($survey_d3_data, function($a, $b) {
      return $a['version'] < $b['version'];
    });

    return $survey_d3_data;
  }

  /************************************************/

  public static function getD3DatesDataFromSurveyVersions($versions) {
    $tmp_d3_dates_data = array_reduce($versions, function($acc, $version) {
      $acc[$version['version']] = array_reduce($version['answers_sessions'], function($acc, $answer_session) {
        $date = substr($answer_session->created_at->toDateTimeString(), 0, 10);

        if(!isset($acc[$date])):
          $acc[$date] = 0;
        endif;
        $acc[$date]++;

        return $acc;
      }, []);

      return $acc;
    }, []);

    $d3_dates_data = [];

    foreach($tmp_d3_dates_data as $version => $data):
      foreach($data as $date => $answers):
        if(!isset($d3_dates_data[$version])):
          $d3_dates_data[$version] = [];
        endif;

        $d3_dates_data[$version] []= [
          'date' => $date,
          'answers' => $answers
        ];
      endforeach;
    endforeach;

    return $d3_dates_data;
  }

  /************************************************/

  public static function getD3BrowsersDataFromSurveyVersions($versions) {
    return array_reduce($versions, function($acc, $version) {
      $acc[$version['version']] = array_reduce($version['answers_sessions'], function($acc, $answer_session) {
        if(!(isset($acc[$answer_session->user_agent['browser']]))):
          $acc[$answer_session->user_agent['browser']] = 0;
        endif;

        $acc[$answer_session->user_agent['browser']]++;

        return $acc;
      }, []);

      return $acc;
    }, []);
  }

  /************************************************/

  public static function getD3PlatformDataFromSurveyVersions($versions) {
    return array_reduce($versions, function($acc, $version) {
      $acc[$version['version']] = array_reduce($version['answers_sessions'], function($acc, $answer_session) {
        if(!(isset($acc[$answer_session->user_agent['platform']]))):
          $acc[$answer_session->user_agent['platform']] = 0;
        endif;

        $acc[$answer_session->user_agent['platform']]++;

        return $acc;
      }, []);

      return $acc;
    }, []);
  }

  /************************************************/

  public static function getSurveyByShareableLink($s_link) {
    return (
        $survey = self::where('shareable_link', '=', $s_link)->limit(1)->get()
      ) &&
        count($survey) === 1
      ? $survey[0]
      : null
    ;
  }
}

