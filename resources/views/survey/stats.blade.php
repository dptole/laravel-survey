@extends('main')

@section('title', '/ Survey statistics')

@section('content')
  <h1 class="title m-b-md text-center text-warning bg-warning lar-refresh-survey hide">
    {{ Helper::linkRoute('survey.stats', 'New data available, refresh the page', [$survey->uuid], ['class' => 'btn btn-info btn-block']) }}
  </h1>

  <h1 class="title m-b-md text-center">
    Statistics
  </h1>

  <div class="row">
    <div class="col-xs-12 stats-container">
      <div class="row">
        <div class="col-xs-12">
          Total answers: {{ $survey->total_answers }} ({{ $survey->{'fully_answered_%'} }} fully answered)
        </div>
      </div>

      <div class="text-center svg-container">
        <span class="svg-loader">Loading graph...</span>
      </div>

      <div class="col-xs-12 lar-overflow">
        @foreach($survey->versions as $version)
        <table class="table bordered hide table-versions table-version-{{ $version['version'] }}" data-survey-version="{{ $version['version'] }}">
          <caption><h2>Survey version {{ $version['version'] }}</h2></caption>
          <thead>
            <tr>
              <th class="svg-answer-date html-clickable">Answer date</th>
              <th class="html-clickable">
                <span data-toggle="tooltip" data-placement="bottom" title="From the Accept-Language HTTP header">
                  Possible languages<sup>?</sup>
                </span>
              </th>
              <th class="html-clickable svg-answer-country">
                <span data-toggle="tooltip" data-placement="bottom" title="From JavaScript date timezone">
                  Possible countries<sup>?</sup>
                </span>
              </th>
              <th class="html-clickable svg-answer-browser">Browser</th>
              <th class="html-clickable svg-answer-platform">Platform</th>
              <th class="html-clickable svg-answer-completeness">Completeness</th>
            </tr>
          </thead>
          <tbody>
            @foreach($version['answers_sessions'] as $answer_session)
            <tr class="html-clickable lar-user-answer" data-table-user-info="{{ $answer_session->session_uuid }}">
              <td>
                {{ Helper::createCarbonDiffForHumans($answer_session->created_at) }}
              </td>
              <td>
                {{ Helper::lsrGetLanguageRegions($answer_session['request_info']->headers->{'accept-language'}[0]) }}
              </td>
              <td>
                {{ Helper::tzGetCountries($answer_session->request_info->js->date->timezone) }}
              </td>
              <td>
                {{ $answer_session['user_agent']['browser'] }}
              </td>
              <td>
                {{ $answer_session['user_agent']['platform'] }}
              </td>
              <td>
                {{ $answer_session['total_answered_%'] }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endforeach
      </div>

      <div class="col-xs-12 lar-overflow">
        @foreach($survey->versions as $version)
          @foreach($version['answers_sessions'] as $answer_session)
          <div class="table-users-info hide table-user-info-{{ $answer_session->session_uuid }}">
            <h2>Survey version {{ $answer_session->version }}</h2>

            <ul class="nav nav-tabs" role="tablist">
              <li role="presentation" class="active"><a href="#lar-tab-user-info-{{ $answer_session->session_uuid }}" aria-controls="home" role="tab" data-toggle="tab">User info</a></li>
              <li role="presentation"><a href="#lar-tab-user-answers-{{ $answer_session->session_uuid }}" aria-controls="profile" role="tab" data-toggle="tab">Answers</a></li>
            </ul>

            <div class="tab-content">
              <div role="tabpanel" class="tab-pane active" id="lar-tab-user-info-{{ $answer_session->session_uuid }}">
                <table class="table bordered">
                  <tbody>
                    <tr>
                      <td colspan="2">
                        <button data-survey-version="{{ $answer_session->version }}" class="lar-user-info-return btn btn-primary">Return</button>
                      </td>
                    </tr>
                    
                    <tr>
                      <th width="30%">Answer date</th>
                      <td>{{ Helper::createCarbonDiffForHumans($answer_session->created_at) }}</td>
                    </tr>
                    
                    <tr>
                      <th>Browser</th>
                      <td>
                        {{ $answer_session->user_agent['browser'] }}
                      </td>
                    </tr>
                    
                    <tr>
                      <th>Platform</th>
                      <td>
                        {{ $answer_session->user_agent['platform'] }}
                      </td>
                    </tr>
                    
                    <tr>
                      <th>Completeness</th>
                      <td>{{ $answer_session['total_answered_%'] }}</td>
                    </tr>
                    
                    <tr>
                      <th>Answered at (server time)</th>
                      <td>{{ $answer_session->created_at }}</td>
                    </tr>
                    
                    <tr>
                      <th>Screen width</th>
                      <td>{{ $answer_session->request_info->js->window->width }}</td>
                    </tr>
                    
                    <tr>
                      <th>Screen height</th>
                      <td>{{ $answer_session->request_info->js->window->height }}</td>
                    </tr>
                    
                    <tr>
                      <th>Local time</th>
                      <td>
                        {{ $answer_session->request_info->js->date->date_string }}
                        {{ $answer_session->request_info->js->date->time_string }}
                      </td>
                    </tr>
                    
                    <tr>
                      <th>DB-IP Country info</th>
                      <td class="lar-country-info-box" data-answer-session-uuid="{{ $answer_session->session_uuid }}">
                        <table class="table bordered lar-has-country-info hide"></table>

                        <div class="lar-hasnt-country-info">
                          <span class="glyphicon glyphicon-refresh animation-spin hide lar-loading-country-info" aria-hidden="true"></span>
                          <button class="btn btn-primary lar-fetch-country-info" data-answer-session-id="{{ $answer_session->id }}" data-answer-session-uuid="{{ $answer_session->session_uuid }}" data-survey-version="{{ $answer_session->version }}" data-answer-session-ip="{{ Helper::getIpFromRequestInfo($answer_session->request_info) }}">Fetch</button>
                        </div>
                      </td>
                    </tr>

                    @if(isset($maxmind[$answer_session->id]) && count($maxmind[$answer_session->id]) > 0)
                    <tr>
                      <th>Maxmind GeoIP2</th>
                      <td>
                        <table class="table bordered">
                          <tbody>
                            @foreach($maxmind[$answer_session->id] as $key => $value)
                            <tr>
                              <th>{{ $key }}</th>
                              <td>{{ $value }}</td>
                            </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    @endif

                    @if(property_exists($answer_session->request_info->headers, 'dnt'))
                    <tr>
                      <th>Do not track?</th>
                      <td>Yes</td>
                    </tr>
                    @endif
                    
                    @if(
                      property_exists($answer_session->request_info->js, 'connection') &&
                      property_exists((object)$answer_session->request_info->js->connection, 'effectiveType')
                    )
                    <tr>
                      <th>Connection info</th>
                      <td>
                        <table class="table bordered">
                          <tbody>
                            <tr>
                              <th width="30%">Effective type</th>
                              <td>{{ $answer_session->request_info->js->connection->effectiveType }}</td>
                            </tr>
                            
                            <tr>
                              <th>Downlink</th>
                              <td>{{ $answer_session->request_info->js->connection->downlink }} Mbps</td>
                            </tr>
                            
                            <tr>
                              <th>RTT</th>
                              <td>{{ $answer_session->request_info->js->connection->rtt }} ms</td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    @endif
                    
                    @if($answer_session->request_info->js->battery->success)
                    <tr>
                      <th>Laptop info</th>
                      <td>
                        <table class="table bordered">
                          <tbody>
                            <tr>
                              <th width="30%">Is charging?</th>
                              <td>{{ $answer_session->request_info->js->battery->result->charging ? 'Yes' : 'No' }}</td>
                            </tr>

                            @if(!$answer_session->request_info->js->battery->result->charging)
                            <tr>
                              <th>Discharging time</th>
                              <td>
                                {{ $answer_session->request_info->js->battery->result->dischargingTime / 60 / 60 | 0 }}h
                                {{ $answer_session->request_info->js->battery->result->dischargingTime / 60 % 60     }}m
                              </td>
                            </tr>
                            @endif
                            
                            <tr>
                              <th>Level</th>
                              <td>{{ $answer_session->request_info->js->battery->result->level * 100 }}%</td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    @endif
                  </tbody>
                </table>
              </div>

              <div role="tabpanel" class="tab-pane" id="lar-tab-user-answers-{{ $answer_session->session_uuid }}">
                <table class="table bordered">
                  <tbody>
                    <tr>
                      <td colspan="2">
                        <button data-survey-version="{{ $answer_session->version }}" class="lar-user-info-return btn btn-primary">Return</button>
                      </td>
                    </tr>
                    
                    @if($answer_session['joined_questions_and_answers'])
                      @foreach($answer_session['joined_questions_and_answers'] as $joined_question_and_answer)
                      <tr>
                        <th width="30%">Question {{ $joined_question_and_answer->order }}</th>
                        <td>{{ $joined_question_and_answer->description_question }}</td>
                      </tr>
                      <tr>
                        @if($joined_question_and_answer->type === 'check')
                          <th>Selected answer</th>
                          <td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {{ $joined_question_and_answer->description_option }}</td>
                        @else
                          <th>Typed answer</th>
                          <td><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> {{ $joined_question_and_answer->free_text }}</td>
                        @endif
                      </tr>
                      @endforeach
                    @else
                      <tr>
                        <td colspan="2">
                          <h3>No answers were given</h3>
                        </td>
                      </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>

          </div>

          @endforeach
        </div>

        @foreach($survey->versions as $version)
        <div class="row stats-version-container hide">
          <div class="col-xs-12">
            Version: {{ $version['version'] }}
          </div>
          <div class="col-xs-12">
            <ul>
              <li>Answers: {{ count($version['answers_sessions']) }} ({{ $version['fully_answered_%'] }} fully answered)</li>
            </ul>
          </div>
        </div>
        @endforeach
      @endforeach
    </div>

    <div class="col-xs-12">
      <div class="row">
        <div class="pull-right col-sm-4 col-xs-12 form-group">
          {{ Helper::linkRoute('survey.edit', 'Back', [$survey->uuid], ['class' => 'btn-block btn btn-default']) }}
        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    var $d3_data = {
      answers: {!! $d3_answers_data !!},
      dates: {!! $d3_dates_data !!},
      platforms: {!! $d3_platform_data !!},
      browsers: {!! $d3_browsers_data !!}
    };
    var $country_info = {!! $country_info !!};
    var $survey_uuid = "{{ $survey->uuid }}";
    var $world_map_url = "{{ asset('laravel/images/jpg/world-map.jpg') }}";
  </script>
  <script type="text/javascript" src="{{ Helper::route('stats') }}"></script>
@endsection

