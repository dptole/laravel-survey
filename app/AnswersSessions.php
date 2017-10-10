<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

class AnswersSessions extends Model
{
    public static function createSession($request_info = '') {
        $answers_sessions = new AnswersSessions;
        $answers_sessions->session_id = Uuid::generate(4)->string;
        $answers_sessions->request_info = $request_info;
        $answers_sessions->save();
        return $answers_sessions->session_id;
    }

    public static function getIdByUuid($uuid) {
        return (
          $answers_sessions = AnswersSessions::where('session_id', '=', $uuid)
            ->limit(1)
            ->get()
            ->all()
        ) &&
          is_array($answers_sessions) &&
          count($answers_sessions) === 1
          ? $answers_sessions[0]->id
          : 0
        ;
    }
}
