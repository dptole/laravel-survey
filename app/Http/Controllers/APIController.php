<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;
use App\Surveys;

class APIController extends Controller {
  /**
   * Generates a session id so that the users are tracked when answering the questions.
   * 
   * @return {"session_id":"82e55c09-54e1-4628-bb7e-92a7580c4273"}
   */
  public function getSessionId($s_uuid, Request $request) {
    if(!Surveys::getByUuid($s_uuid)):
      return response()->json([
        'error' => 'Invalid survey.'
      ], 400);
    endif;

    //@TODO save this session id along with the survey id to another database
    // where the answers will be stored.
    // Make use of $request->cookie('laravel_session') so that the same user
    // cannot start a survey in the middle.
    return response()->json([
      'session_id' => Uuid::generate(4)->string
    ]);
  }
}

