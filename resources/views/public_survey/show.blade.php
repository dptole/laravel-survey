@extends('main')

@section('title', '/ Survey ' . $survey->name)

@section('content')
  <h1 class="text-center">Welcome to the survey &quot;{{ $survey->name }}&quot;</h1>

  <table class="table">
    <thead>
      <tr>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          {!! nl2br(e($survey->description)) !!}
        </td>
      </tr>
      <tr>
        <td>
          <button class="btn btn-success btn-block">Start</button>
        </td>
      </tr>
    </tbody>
  </table>
@endsection

