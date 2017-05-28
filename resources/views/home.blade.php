@extends('main')

@section('title', '/ Home')

@section('content')
  <h1 class="text-center">Create your own surveys with Laravel!</h1>

  <table class="table table-hover table-bordered">
    <thead>
      <tr>
        <th>Name</th>
        <th>Author</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($available_surveys as $available_survey)
        <tr>
          <td>{{ $available_survey->name }}</td>
          <td>{{ $available_survey->author_name }}</td>
          <td>
            <div class="pull-right">
              <button class="btn btn-xs btn-primary">Take this survey</button>
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection

