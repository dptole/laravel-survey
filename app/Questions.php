<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Surveys;

class Questions extends Model {
  public static function getBySurvey($q_uuid, $id) {
    return (
        $questions = Questions::where([
          'uuid' => $q_uuid,
          'survey_id' => $id
        ])->get()
      ) &&
        count($questions) === 1
      ? $questions[0]
      : null
    ;
  }

  public static function getAllByOwner($id) {
    return Questions::where('survey_id', '=', $id)
      ->orderBy('updated_at', 'desc')
      ->paginate(5)
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
}

