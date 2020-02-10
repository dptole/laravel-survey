<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionsOptionsView extends Model {
  protected $table = 'questions_options_view';

  public static function getById($question_id) {
    return (
      $last_version = self::where('question_id', '=', $question_id)
        ->limit(1)
        ->get()
        ->all()
    ) &&
      is_array($last_version) &&
      count($last_version) === 1
        ? $last_version[0]
        : null
    ;
  }
}
