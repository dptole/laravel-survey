<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Surveys;
use Webpatser\Uuid\Uuid;

class SurveyController extends Controller {
  /**
   * Show the survey creation page.
   *
   * @return \Illuminate\Http\Response
   */
  public function create() {
    return view('survey.create');
  }

  /**
   * Create a new survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request) {
    $this->validate($request, [
      'name' => 'required|max:127|min:3'
    ]);

    $survey = new Surveys;
    $survey->user_id = $request->user()->id;
    $survey->name = $request->input('name');
    $survey->uuid = Uuid::generate(4);
    $survey->description = $request->input('description');
    $survey->save();
    $request->session()->flash('success', 'Survey ' . $survey->uuid . ' successfully created!');
    return redirect()->route('survey.edit', $survey->uuid);
  }

  /**
   * Show survey editing page.
   *
   * @return \Illuminate\Http\Response
   */
  public function edit($uuid, Request $request) {
    $survey = Surveys::where('user_id', '=', $request->user()->id)
      ->where('uuid', '=', $uuid)
      ->limit(1)
      ->get()
    ;

    if(count($survey) !== 1):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('dashboard');
    endif;

    return view('survey.edit')->with([
      'survey' => $survey[0],
      'questions' => []
    ]);
  }

  /**
   * Update the survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function update($uuid, Request $request) {
    $this->validate($request, [
      'name' => 'required|max:127|min:3'
    ]);

    $survey = Surveys::where('user_id', '=', $request->user()->id)
      ->where('uuid', '=', $uuid)
      ->limit(1)
      ->get()
    ;

    if(count($survey) !== 1):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('dashboard');
    endif;

    $survey = $survey[0];
    $survey->name = $request->input('name');
    $survey->description = $request->input('description');
    $survey->save();
    $request->session()->flash('success', 'Survey ' . $survey->uuid . ' successfully updated!');
    return redirect()->route('dashboard');
  }

  /**
   * Delete the survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function destroy($uuid, Request $request) {
    $deleted = Surveys::where([
      'user_id' => $request->user()->id,
      'uuid' => $uuid
    ])->delete();

    if($deleted):
      $request->session()->flash('success', 'Survey "' . $uuid . '" successfully removed!');
    else:
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
    endif;

    return redirect()->route('dashboard');
  }
}

