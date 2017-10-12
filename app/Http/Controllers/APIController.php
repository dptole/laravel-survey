<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Surveys;
use App\Answers;
use App\AnswersSessions;
use App\AnswersBehavior;
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

    return response()->json([
      'session_id' => AnswersSessions::createSession(json_encode([
        'js' => $request->input(),
        'headers' => $request->header(),
        'ips' => $request->ips()
      ]))
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
      $request_info,
      $answers_session_id
    ) = array(
      null,
      $request->input('survey_id'),
      $request->input('question_id'),
      $request->input('question_option_id'),
      $request->input('free_text'),
      json_encode([
        'headers' => $request->header(),
        'ips' => $request->ips()
      ]),
      AnswersSessions::getIdByUuid($request->input('answers_session_id'))
    );

    $answer = new Answers;
    $answer->survey_id = $survey_id;
    $answer->question_id = $question_id;
    $answer->question_option_id = $question_option_id;
    $answer->free_text = is_string($free_text) ? $free_text : '';
    $answer->request_info = $request_info;
    $answer->answers_session_id = $answers_session_id;
    $answer->save();
    return response()->json(true);
  }

  /**
   * Saves user behavior when answering the survey.
   *
   * @return {"success":true}
   */
  public function saveBehavior(Request $request) {
    $answers_behavior = new AnswersBehavior;
    $answers_behavior->answers_session_id = AnswersSessions::getIdByUuid($request->input('answers_session_id'));
    $answers_behavior->behavior = json_encode($request->input('behavior'));
    $answers_behavior->save();

    return response()->json(true);
  }
}

