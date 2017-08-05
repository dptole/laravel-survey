@extends('main')

@section('title', '/ Survey ' . $survey->name)

@section('content')
  <div class="public-survey-content" data-survey="{{ json_encode($survey) }}"></div>
  <script type="text/javascript" src="{{ Helper::route('start-survey') }}"></script>
@endsection

