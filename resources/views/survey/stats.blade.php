@extends('main')

@section('title', '/ Survey statistics')

@section('content')
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
              <th class="html-clickable">
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

                @if(Helper::getDbIpUrlFromRequestInfo($answer_session->request_info))
                  <a target="_blank" href="{{ Helper::getDbIpUrlFromRequestInfo($answer_session->request_info) }}">
                    More information
                  </a>
                @endif
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
          <table class="table bordered hide table-users-info table-user-info-{{ $answer_session->session_uuid }}">
            <caption><h2>User info</h2></caption>
            
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
                <th>IP Address</th>
                <td>
                  {{ Helper::getIpFromRequestInfo($answer_session->request_info) }}
                </td>
              </tr>
              
              <tr>
                <th>Country info</th>
                <td>
                  @if(property_exists($answer_session->request_info, 'db-ip'))
                  @foreach($answer_session->request_info->{'db-ip'} as $key => $value)
                  <table class="table bordered">
                    <tbody>
                      <tr>
                        <th width="30%">{{ $key }}</th>
                        <td>{{ $value }}</td>
                      </tr>
                    </tbody>
                  </table>
                  @endforeach
                  @else
                    <span data-toggle="tooltip" data-placement="top" title="Not yet implemented">
                      <a href="javascript:void 0">Fetch</a>
                    </span>
                  @endif
                </td>
              </tr>
              
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
                        <td>{{ $answer_session->request_info->js->connection->downlink }} MB/s</td>
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
          {{ Html::linkRoute('survey.edit', 'Back', [$survey->uuid], ['class' => 'btn-block btn btn-default']) }}
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
    }
  </script>
  <script type="text/javascript" src="{{ Helper::route('stats') }}"></script>
@endsection

