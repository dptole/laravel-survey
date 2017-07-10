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
    return DB::select('
      SELECT
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
              SELECT id AS sub_id
              FROM questions
              WHERE uuid = ?
            ) AS sub_questions
          WHERE
            sub_questions.sub_id < questions.id
          LIMIT
            1
        ) AS questions
      ON
        surveys.id = questions.survey_id
      WHERE
        surveys.id = ?
      ',
      [
        $q_uuid,
        $s_id
      ]
    );
  }

  public static function deleteByOwner($s_uuid, $q_uuid, $user_id) {
    return ($survey = Surveys::getByOwner($s_uuid, $user_id)) &&
      Questions::where([
        'survey_id' => $survey->id,
        'uuid' => $q_uuid
      ])->delete()
    ;
  }
}

