<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper;
use App\Surveys;
use App\Questions;
use App\QuestionsOptions;

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

    $survey->all_questions = Questions::getAllBySurveyIdOrdered($survey->id);
    if(!($survey->all_questions && count($survey->all_questions) > 0)):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" does not have questions.');
      return redirect()->route('home');
    endif;

    foreach($survey->all_questions as &$question):
      $question->answers = QuestionsOptions::getAllByQuestionId($question->id);
      if(!($question->answers && count($question->answers) > 0)):
        $request->session()->flash('warning', 'There are some incomplete questions.');
        return redirect()->route('dashboard');
      endif;
    endforeach;

    Helper::broadcast('public-survey', 'new-user', [
      'user' => [
        'headers' => $request->header(),
        'ips' => $request->ips()
      ]
    ]);

    return view('public_survey.show')->withSurvey($survey);
  }

  /**
   * Display the start survey main page.
   *
   * @return \Illuminate\Http\Response
   */
  public function shareableLink($s_link, Request $request) {
    $survey = Surveys::getSurveyByShareableLink($s_link);

    if(!$survey):
      $request->session()->flash('warning', 'Survey "' . $s_link . '" was not found.');
      return redirect()->route('home');
    endif;

    return redirect()->route('public_survey.show', $survey->uuid);
  }
}

