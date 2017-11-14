@extends('main')

@section('title', '/ Survey statistics')

@section('content')
  <h1 class="title m-b-md text-center">
    Statistics
  </h1>

  <div class="row">
    <div class="col-xs-12">
      Total answers: {{ $survey->total_answers }} ({{ $survey->{'fully_answered_%'} }} fully answered)
    </div>
  </div>

  @foreach($survey->versions as $version)
  <div class="row stats-version-container">
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

  <script type="text/javascript" src="{{ Helper::route('stats') }}"></script>
@endsection

