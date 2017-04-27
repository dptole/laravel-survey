<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

class QuestionsOptions extends Model {
  public static function getAllByQuestionId($id) {
    return QuestionsOptions::where('question_id', '=', $id)->get()->all();
  }

  public static function getAllByQuestionIdAsJSON($id) {
    return array_values(array_map(function($question) {
      return [
        'value' => $question->description,
        'type' => $question->type
      ];
    }, QuestionsOptions::getAllByQuestionId($id)));
  }

  public static function deleteAllByQuestionId($id) {
    return QuestionsOptions::where('question_id', '=', $id)->delete();
  }

  public static function saveArray($question_id, $questions_options) {
    if(!(is_array($questions_options) && is_numeric($question_id))):
      return false;
    endif;

    QuestionsOptions::deleteAllByQuestionId($question_id);

    foreach($questions_options as $question_option):
      $questions_options = new QuestionsOptions;
      $questions_options->question_id = $question_id;
      $questions_options->description = $question_option['type'] !== 'check' ? '' : $question_option['value'];
      $questions_options->type = $question_option['type'];
      $questions_options->uuid = Uuid::generate(4);
      $questions_options->save();
    endforeach;

    return true;
  }
}

