<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Surveys;
use App\Questions;

class PublicSurveyController extends Controller {
  /**
   * Display the start survey main page.
   *
   * @return \Illuminate\Http\Response
   */
  public function show($uuid, Request $request) {
    $survey = Surveys::getByUuid($uuid);

    if(!$survey):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('home');
    elseif($survey->is_running !== true):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" is not running.');
      return redirect()->route('home');
    endif;

    $question = Questions::getFirstQuestionBySurveyId($survey->id);
    if(!$question):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" does not have questions.');
      return redirect()->route('home');
    endif;

    $survey->first_question = $question;

    return view('public_survey.show')->withSurvey($survey);
  }

  /**
   * Start the survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function start($s_uuid, $q_uuid, Request $request) {
    $survey = Surveys::getByUuid($s_uuid);

    if(!$survey):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('home');
    elseif($survey->is_running !== true):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" is not running.');
      return redirect()->route('home');
    endif;

    $next_question = Questions::getNextQuestionBySurveyId($survey->id, $q_uuid);
    $survey->next_question = is_array($next_question) ? $next_question[0] : null;

    return view('public_survey.start')->withSurvey($survey);
  }
}

