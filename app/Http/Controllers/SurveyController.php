<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper;
use App\Surveys;
use App\Questions;
use App\AnswersSessions;
use App\SurveysLastVersionsView;
use Webpatser\Uuid\Uuid;
use Jenssegers\Agent\Agent;

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
    $versions = Surveys::getVersions($survey);

    if(count($versions) < 1):
      $request->session()->flash('warning', 'No data available for survey "' . $s_uuid . '".');
      return redirect()->route('dashboard');
    endif;

    $country_info = [];
    $global_answers_sessions = 0;
    $global_answers = 0;

    $maxmind_props = [
      'Timezone' => 'LOCATION_TIME_ZONE',
      'Longitude' => 'LOCATION_LONGITUDE',
      'Latitude' => 'LOCATION_LATITUDE',
      'Subdivision ISO code' => 'EN_SUBDIVISIONS_ISO_CODE',
      'Postal code' => 'POSTAL_CODE',
      'City' => 'EN_CITY_NAME',
      'ASN name' => 'ASN_NAME',
      'ASN code' => 'ASN_CODE',
      'Continent' => 'EN_CONTINENT_NAME',
      'Country name' => 'EN_COUNTRY_NAME',
      'Country code' => 'COUNTRY_CODE'
    ];

    $maxmind = [];

    foreach($versions as &$version):
      foreach($version['answers_sessions'] as $answer_session):
        if(property_exists($answer_session->request_info, 'server')):
          $maxmind[$answer_session->id] = [];
          foreach($maxmind_props as $name => $key):
            $ip_key = 'MM_IP_' . $key;
            $header_key = 'MM_HEADER_' . $key;

            if(
              property_exists($answer_session->request_info->server, $header_key) &&
              strlen($answer_session->request_info->server->$header_key) > 0
            ):
              $maxmind[$answer_session->id][$name] = $answer_session->request_info->server->$header_key;

            elseif(
              property_exists($answer_session->request_info->server, $ip_key) &&
              strlen($answer_session->request_info->server->$ip_key) > 0
            ):
              $maxmind[$answer_session->id][$name] = $answer_session->request_info->server->$ip_key;

            endif;
          endforeach;
        endif;
      endforeach;

      $questions = $version['questions'];
      $total_questions = count($version['questions']);
      $global_answers_sessions += count($version['answers_sessions']);

      $total_answers_sessions = count($version['answers_sessions']);
      $total_answers = array_reduce(
        $version['answers_sessions'],
        function($accumulator, $answer_session) use ($total_questions, &$global_answers, $questions, &$country_info) {
          $fully_answered = count($answer_session['answers']) >= $total_questions;
          $global_answers += $fully_answered;

          if(property_exists($answer_session->request_info, 'db-ip')):
            if(!isset($country_info[$answer_session->version])):
              $country_info[$answer_session->version] = [];
            endif;
            $country_info[$answer_session->version][$answer_session->session_uuid] = $answer_session->request_info->{'db-ip'};
          endif;

          $total_answered = count($answer_session['answers']) / $total_questions * 100;
          if($total_answered > 100):
            $total_answered = 100;
          endif;
          $answer_session['total_answered_%'] = sprintf('%.2f', $total_answered) . '%';

          if(
            property_exists($answer_session['request_info']->headers, 'user-agent') &&
            is_array($answer_session['request_info']->headers->{'user-agent'}) &&
            count($answer_session['request_info']->headers->{'user-agent'}) === 1 &&
            is_string($answer_session['request_info']->headers->{'user-agent'}[0])
          ):
            $agent = new Agent;
            $agent->setUserAgent($answer_session['request_info']->headers->{'user-agent'}[0]);
            $agent->setHttpHeaders($answer_session['request_info']->headers);

            $answer_session['user_agent'] = [
              'browser' => $agent->browser(),
              'platform' => $agent->platform()
            ];
          else:
            $answer_session['user_agent'] = [
              'browser' => 'Unknown',
              'platform' => 'Unknown'
            ];
          endif;

          $answer_session['joined_questions_and_answers'] = AnswersSessions::joinQuestionsAndAnswers($answer_session['survey_id'], $answer_session['id']);

          return $accumulator + $fully_answered;
        },
        0
      );

      $version['fully_answered'] = $total_answers;
      $version['fully_answered_%'] = (
        $total_answers_sessions > 0
          ? sprintf('%.2f', $version['fully_answered'] / $total_answers_sessions * 100)
          : 0
      ) . '%';

      $version['not_fully_answered'] = $total_answers_sessions - $total_answers;
      $version['not_fully_answered_%'] = (
        $total_answers_sessions > 0
          ? sprintf('%.2f', $version['not_fully_answered'] / $total_answers_sessions * 100)
          : 0
      ) . '%';
    endforeach;

    $survey->versions = $versions;
    $survey->fully_answered = $global_answers;
    $survey->{'fully_answered_%'} = (
      $global_answers_sessions > 0
        ? sprintf('%.2f', $survey->fully_answered / $global_answers_sessions * 100)
        : 0
     ) . '%';

    $survey->not_fully_answered = $global_answers_sessions - $global_answers;
    $survey->{'not_fully_answered_%'} = (
      $global_answers_sessions > 0
        ? sprintf('%.2f', $survey->not_fully_answered / $global_answers_sessions * 100)
        : 0
    ) . '%';

    $d3_answers_data = Surveys::getD3AnswersDataFromSurveyVersions($survey->versions);
    $d3_dates_data = Surveys::getD3DatesDataFromSurveyVersions($survey->versions);
    $d3_browsers_data = Surveys::getD3BrowsersDataFromSurveyVersions($survey->versions);
    $d3_platform_data = Surveys::getD3PlatformDataFromSurveyVersions($survey->versions);

    return view('survey.stats')->with([
      'survey' => $survey,
      'country_info' => json_encode($country_info),
      'd3_platform_data' => json_encode($d3_platform_data),
      'd3_browsers_data' => json_encode($d3_browsers_data),
      'd3_answers_data' => json_encode($d3_answers_data),
      'd3_dates_data' => json_encode($d3_dates_data),
      'maxmind' => $maxmind
    ]);
  }
}

