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
        <table class="table bordered hide table-versions {{ 'table-version-' . $version['version'] }}" data-survey-version="{{ $version['version'] }}">
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
              <th class="html-clickable">Browser</th>
              <th class="html-clickable">Platform</th>
              <th class="html-clickable">Completeness</th>
            </tr>
          </thead>
          <tbody>
            @foreach($version['answers_sessions'] as $answer_session)
            <tr>
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
    var $d3_answers_data = {!! $d3_answers_data !!};
    var $d3_dates_data = {!! $d3_dates_data !!};
  </script>
  <script type="text/javascript" src="{{ Helper::route('stats') }}"></script>
@endsection

