<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Surveys;
use App\Questions;

class QuestionController extends Controller {
  /**
   * Show the question creation page.
   *
   * @return \Illuminate\Http\Response
   */
  public function create($uuid, Request $request) {
    $survey = Surveys::where('user_id', '=', $request->user()->id)
      ->where('uuid', '=', $uuid)
      ->limit(1)
      ->get()
    ;

    if(count($survey) !== 1):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('dashboard');
    endif;

    return view('question.create')->with([
      'survey' => $survey[0]
    ]);
  }

  /**
   * Create a new survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function store($uuid, Request $request) {
    $this->validate($request, [
      'description' => 'required|max:1023|min:4'
    ]);

    return redirect()->route('dashboard');
  }
}

