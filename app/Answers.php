<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answers extends Model
{
    public static function getByAnswersSessionId($answers_session_id)
    {
        return self::where('answers_session_id', '=', $answers_session_id)->get()->all();
    }
}
