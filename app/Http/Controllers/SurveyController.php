<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper;
use App\Surveys;
use App\Questions;
use App\AnswersSessions;
use App\SurveysLastVersionsView;
use Webpatser\Uuid\Uuid;

class SurveyController extends Controller {
  /**
   * Validate the survey.
   */
  public function validateSurvey(Request $request) {
    $this->validate($request, [
      'name' => 'required|max:127|min:3'
    ]);
  }

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
    $this->validateSurvey($request);

    $survey = new Surveys;
    $survey->user_id = $request->user()->id;
    $survey->name = $request->input('name');
    $survey->uuid = Uuid::generate(4);
    $survey->description = $request->input('description');
    $survey->shareable_link = Helper::generateRandomString(8);
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
    $survey = Surveys::getByOwner($uuid, $request->user()->id);

    if(!$survey):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('dashboard');
    endif;

    $survey->version = SurveysLastVersionsView::getById($survey->id);

    return view('survey.edit')->with([
      'survey' => $survey,
      'questions' => Questions::getAllBySurveyIdPaginated($survey->id)
    ]);
  }

  /**
   * Update the survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function update($uuid, Request $request) {
    if(Surveys::isRunning($uuid) === Surveys::ERR_IS_RUNNING_SURVEY_OK):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" cannot be updated because it is being run.');
      return redirect()->route('survey.edit', $uuid);
    endif;

    $this->validateSurvey($request);

    $survey = Surveys::getByOwner($uuid, $request->user()->id);

    if(!$survey):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('dashboard');
    endif;

    $survey->name = $request->input('name');
    $survey->description = $request->input('description');
    $survey->save();
    $request->session()->flash('success', 'Survey ' . $survey->uuid . ' successfully updated!');
    return redirect()->route('survey.edit', $uuid);
  }

  /**
   * Delete the survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function destroy($uuid, Request $request) {
    if(Surveys::isRunning($uuid) === Surveys::ERR_IS_RUNNING_SURVEY_OK):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" cannot be deleted because it is being run.');
      return redirect()->route('survey.edit', $uuid);
    endif;

    $deleted = Surveys::deleteByOwner($uuid, $request->user()->id);

    if($deleted):
      $request->session()->flash('success', 'Survey "' . $uuid . '" successfully removed!');
    else:
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
    endif;

    return redirect()->route('dashboard');
  }

  /**
   * Run the survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function run($uuid, Request $request) {
    $survey = Surveys::getByOwner($uuid, $request->user()->id);
    if(!$survey):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" does not exist.');
      return redirect()->route('dashboard');
    endif;

    $questions = Questions::getAllBySurveyId($survey->id);
    if(!($questions && count($questions) > 0)):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" must have at least one question.');
      return redirect()->route('survey.edit', $uuid);
    endif;

    $status = Surveys::run($uuid, $request->user()->id);

    if($status === Surveys::ERR_RUN_SURVEY_NOT_FOUND):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('dashboard');
    elseif($status === Surveys::ERR_RUN_SURVEY_INVALID_STATUS):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" invalid status, it should be "draft".');
      return redirect()->route('survey.edit', $uuid);
    elseif($status === Surveys::ERR_RUN_SURVEY_ALREADY_RUNNING):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" already running.');
      return redirect()->route('survey.edit', $uuid);
    endif;

    $request->session()->flash('success', 'Survey "' . $uuid . '" is now running.');
    return redirect()->route('survey.edit', $uuid);
  }

  /**
   * Pause the survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function pause($uuid, Request $request) {
    $survey = Surveys::getByOwner($uuid, $request->user()->id);
    if(!$survey):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" not found.');
      return redirect()->route('dashboard');
    endif;

    $questions_next_version = Surveys::generateQuestionsNextVersion($survey);
    foreach($questions_next_version as $question_next_version):
      Questions::createQuestionOptions(
        Questions::createQuestion($question_next_version),
        $question_next_version['questions_options']
      );
    endforeach;

    $status = Surveys::pause($uuid, $request->user()->id);
    if($status === Surveys::ERR_PAUSE_SURVEY_INVALID_STATUS):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" invalid status, it should be "ready".');
      return redirect()->route('survey.edit', $uuid);
    elseif($status === Surveys::ERR_PAUSE_SURVEY_ALREADY_PAUSED):
      $request->session()->flash('warning', 'Survey "' . $uuid . '" is already paused.');
      return redirect()->route('survey.edit', $uuid);
    endif;

    $request->session()->flash('success', 'Survey "' . $uuid . '" is now paused.');
    return redirect()->route('survey.edit', $uuid);
  }

  /**
   * Shows the statistics page of a given survey.
   *
   * @return \Illuminate\Http\Response
   */
  public function stats($s_uuid, Request $request) {
    $survey = Surveys::getByOwner($s_uuid, $request->user()->id);

    if(!$survey):
      $request->session()->flash('warning', 'Survey "' . $s_uuid . '" not found.');
      return redirect()->route('dashboard');
    endif;

    $survey->total_answers = AnswersSessions::countBySurveyId($survey->id);
    $survey->versions = Surveys::getVersions($survey);

    return view('survey.stats')->withSurvey($survey);
  }
}

