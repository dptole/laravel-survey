<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;
use App\Surveys;
use App\Answers;
use App\ApiErrors;

class APIController extends Controller {
  /**
   * Generates a session id so that the users are tracked when answering the questions.
   * 
   * @return {"success":{"session_id":"82e55c09-54e1-4628-bb7e-92a7580c4273"}}
   */
  public function getSessionId($s_uuid, Request $request) {
    if(!Surveys::getByUuid($s_uuid)):
      return response(new ApiErrors('INVALID_SURVEY', $s_uuid));
    endif;

    //@TODO save this session id along with the survey id to another database
    // where the answers will be stored.
    // Make use of $request->cookie('laravel_session') so that the same user
    // cannot start a survey in the middle.
    return response()->json([
      'session_id' => Uuid::generate(4)->string
    ]);
  }

  /**
   * Saves which answer to which question on which survey the given user have chosen.
   * 
   * @return {"success":true}
   */
  public function saveSurveyAnswer(Request $request) {
    list(
      $session_id,
      $survey_id,
      $question_id,
      $question_option_id,
      $free_text,
      $request_info
    ) = array(
      null,
      $request->input('survey_id'),
      $request->input('question_id'),
      $request->input('question_option_id'),
      $request->input('free_text'),
      json_encode([
        'headers' => $request->header(),
        'ips' => $request->ips()
      ])
    );

    $answer = new Answers;
    $answer->survey_id = $survey_id;
    $answer->question_id = $question_id;
    $answer->question_option_id = $question_option_id;
    $answer->free_text = is_string($free_text) ? $free_text : '';
    $answer->request_info = $request_info;
    $answer->save();
    return response()->json(true);
  }
}

