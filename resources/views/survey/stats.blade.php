@extends('main')

@section('title', '/ Survey statistics')

@section('content')
  <h1 class="title m-b-md text-center">
    Statistics
  </h1>

  <div class="row">
    <div class="col-xs-12">
      Total answers: {{ $survey->total_answers }}
    </div>
  </div>

  <script type="text/javascript" src="{{ Helper::route('stats') }}"></script>
@endsection

