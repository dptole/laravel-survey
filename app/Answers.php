<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\AnswersSessions;

class Answers extends Model {
  public static function getByAnswersSessionId($answers_session_id) {
    return Answers::where('answers_session_id', '=', $answers_session_id)
      ->get()
      ->all()
    ;
  }
}
