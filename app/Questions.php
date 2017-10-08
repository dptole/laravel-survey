<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Surveys;
use DB;

class Questions extends Model {
  public static function getBySurvey($q_uuid, $s_id) {
    return (
        $questions = Questions::where([
          'uuid' => $q_uuid,
          'survey_id' => $s_id
        ])->get()
      ) &&
        count($questions) === 1
      ? $questions[0]
      : null
    ;
  }

  public static function getAllByOwner($s_id) {
    return Questions::where('survey_id', '=', $s_id)
      ->orderBy('updated_at', 'desc')
      ->paginate(5)
    ;
  }

  public static function getAllBySurveyIdUnpaginated($s_id) {
    return Questions::where('survey_id', '=', $s_id)
      ->orderBy('order', 'asc')
      ->get()
      ->all()
    ;
  }

  public static function getAllBySurveyId($s_id, $start_from = 0) {
    return Questions::where('survey_id', '=', $s_id)
      ->orderBy('id', 'asc')
      ->get()
    ;
  }

  public static function getAllBySurveyIdOrdered($s_id, $start_from = 0) {
    return Questions::where('survey_id', '=', $s_id)
      ->orderBy('order', 'asc')
      ->get()
    ;
  }

  public static function deleteByOwner($s_uuid, $q_uuid, $user_id) {
    return ($survey = Surveys::getByOwner($s_uuid, $user_id)) &&
      Questions::where([
        'survey_id' => $survey->id,
        'uuid' => $q_uuid
      ])->delete()
    ;
  }

  public static function getByUuid($uuid) {
    return (
      $questions = Questions::where('uuid', '=', $uuid)->get()
    ) &&
      count($questions) === 1
      ? $questions[0]
      : null
    ;
  }

  public static function getNextInOrder($s_id) {
    return (
      $question = Questions::where('survey_id', '=', $s_id)
      ->orderBy('order', 'desc')
      ->limit(1)
      ->get()
      ->all()
    ) &&
      is_array($question) &&
      count($question) === 1
      ? $question[0]->order + 1
      : 1
    ;
  }

  public static function updateOrder($id, $order) {
    $question = Questions::find($id);
    if(!$question):
      return false;
    endif;
    $question->order = $order;
    $question->save();
    return true;
  }
}

