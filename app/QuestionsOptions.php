<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

class QuestionsOptions extends Model
{
    public static function getAllByQuestionId($id)
    {
        $last_version = QuestionsOptionsView::where('question_id', '=', $id)->limit(1)->get()->all();

        return is_array($last_version) && count($last_version) === 1
      ? self::where([
          'question_id' => $id,
          'version'     => $last_version[0]->last_version,
      ])->get()->all()
      : [];
    }

    public static function getAllByQuestionIdAsJSON($id)
    {
        return array_values(array_map(function ($question) {
            return [
                'value' => $question->description,
                'type'  => $question->type,
            ];
        }, self::getAllByQuestionId($id)));
    }

    public static function saveArray($question_id, $questions_options)
    {
        if (!(is_array($questions_options) && is_numeric($question_id))) {
            return false;
        }

        $last_version = QuestionsOptionsView::where('question_id', '=', $question_id)->limit(1)->get()->all();
        $lv = 0;
        if (is_array($last_version) && count($last_version) === 1) {
            $lv = $last_version[0]->last_version;
        }

        foreach ($questions_options as $question_option) {
            $questions_options = new self();
            $questions_options->question_id = $question_id;
            $questions_options->description = $question_option['type'] !== 'check' ? '' : $question_option['value'];
            $questions_options->type = $question_option['type'];
            $questions_options->uuid = Uuid::generate(4);
            $questions_options->version = $lv + 1;
            $questions_options->save();
        }

        return true;
    }

    public static function getAllByVersion($question_id, $version)
    {
        return self::where([
            'version'     => $version,
            'question_id' => $question_id,
        ])->orderBy('id')->get()->all();
    }
}
