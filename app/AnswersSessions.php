<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;
use App\SurveysLastVersionsView;
use DB;

class AnswersSessions extends Model
{
  public static function createSession($survey_id, $request_info = '') {
    $answers_sessions = new AnswersSessions;
    $answers_sessions->session_uuid = Uuid::generate(4)->string;
    $answers_sessions->survey_id = $survey_id;
    $answers_sessions->request_info = $request_info;
    $answers_sessions->version = SurveysLastVersionsView::getById($survey_id)->last_version;
    $answers_sessions->save();
    return $answers_sessions->session_uuid;
  }

  public static function getIdByUuid($uuid) {
    return (
      $answers_sessions = AnswersSessions::where('session_uuid', '=', $uuid)
        ->limit(1)
        ->get()
        ->all()
    ) &&
      is_array($answers_sessions) &&
      count($answers_sessions) === 1
      ? $answers_sessions[0]->id
      : 0
    ;
  }

  public static function countBySurveyId($survey_id) {
    return AnswersSessions::where('survey_id', '=', $survey_id)->count();
  }

  public static function getBySurveyId($survey_id, $version) {
    return array_map(function($answer_session) {
      $answer_session->answers = Answers::getByAnswersSessionId($answer_session->id);
      $answer_session->request_info = json_decode($answer_session->request_info);
      return $answer_session;
    }, AnswersSessions::where([
      'survey_id' => $survey_id,
      'version' => $version
    ])->orderBy(
      'created_at', 'desc'
    )->get()->all());
  }

  public static function joinQuestionsAndAnswers($survey_id, $answers_session_id) {
    return DB::table('answers')
      ->select('answers.free_text', 'questions.description as description_question', 'questions.order', 'questions.version', 'questions_options.type', 'questions_options.description as description_option')
      ->join('questions', 'questions.id', '=', 'answers.question_id')
      ->join('questions_options', 'questions_options.id', '=', 'answers.question_option_id')
      ->where('answers.survey_id', '=', $survey_id)
      ->where('answers.answers_session_id', '=', $answers_session_id)
      ->orderBy('order', 'asc')
      ->get()
      ->all()
    ;
  }

  public static function getByUuid($answer_session_uuid) {
    return (
      $answer_session = AnswersSessions::where([
        'session_uuid' => $answer_session_uuid
      ])->limit(1)
        ->get()
        ->all()
    ) &&
      is_array($answer_session) &&
      count($answer_session) === 1
        ? $answer_session[0]
        : null
    ;
  }

  public static function updateCountryInfo($answer_session_id, $ip) {
    $answer_session = AnswersSessions::where([
      'id' => $answer_session_id
    ])->limit(1)->get()->all();

    if(count($answer_session) !== 1):
      return false;
    endif;

    $request_info = json_decode($answer_session[0]->request_info);
    if(property_exists($request_info, 'db-ip')):
      return $request_info->{'db-ip'};
    endif;
    $request_info->{'db-ip'} = Helper::dbIpGetIpInfo($ip);

    AnswersSessions::where([
      'id' => $answer_session_id
    ])->update([
      'request_info' => json_encode($request_info)
    ]);

    return $request_info->{'db-ip'};
  }
}
