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

  public static function getFirstQuestionBySurveyId($s_id) {
    return (
      $question = Questions::where('survey_id', '=', $s_id)
        ->orderBy('updated_at', 'asc')
        ->limit(1)
        ->get()
      ) &&
        count($question) === 1
      ? $question[0]
      : null
    ;
  }

  public static function getNextQuestionBySurveyId($s_id, $q_uuid) {
    return (
      $next_question = DB::select(
        'SELECT
          questions.*
        FROM
          surveys
        JOIN
          (
            SELECT
              *
            FROM
              questions,
              (
                SELECT
                  id AS sub_id
                FROM
                  questions
                WHERE
                  uuid = ?
                AND
                  active = 1
              ) AS sub_questions
            WHERE
              sub_questions.sub_id < questions.id
            AND
              questions.active = 1
            LIMIT
              1
          ) AS questions
        ON
          surveys.id = questions.survey_id
        WHERE
          surveys.id = ?
        AND
          questions.active = 1
        ',
        [
          $q_uuid,
          $s_id
        ]
      )
    ) &&
      count($next_question) === 1
      ? $next_question[0]
      : null
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
}

