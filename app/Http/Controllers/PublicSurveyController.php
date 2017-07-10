<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Surveys;

class PublicSurveyController extends Controller {
  public function show($uuid, Request $request) {
    $survey = Surveys::getByUuid($uuid);

    if(!$survey):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('home');
    elseif($survey->is_running !== true):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" is not running.');
      return redirect()->route('home');
    endif;

    return view('public_survey.show')->withSurvey($survey);
  }
}

