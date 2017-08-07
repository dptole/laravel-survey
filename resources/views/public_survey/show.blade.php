@extends('main')

@section('title', '/ Survey ' . $survey->name)

@section('content')
  <div class="public-survey-content" data-survey="{{ json_encode($survey) }}"></div>
  <script type="text/javascript" src="{{ Helper::route('start-survey') }}"></script>
  <style>
    tr.public-survey-answer-row td {
      padding: 0px !important;
    }

    tr.public-survey-answer-row td button {
      width: 100%;
      text-align: left;
      border-radius: 0px;
      background-color: inherit;
    }
  </style>
@endsection

