<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnswersBehavior extends Model {
  public static function getByAnswersSessionId($answers_session_id) {
    return AnswersBehavior::where('answers_session_id', '=', $answers_session_id)
      ->get()
      ->all()
    ;
  }
}
