<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;
use App\SurveysLastVersionsView;

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
      return $answer_session;
    }, AnswersSessions::where([
      'survey_id' => $survey_id,
      'version' => $version
    ])->get()->all());
  }
}
