@extends('main')

@section('title', '/ Home')

@section('content')
  <h1 class="text-center">Create your own surveys with Laravel!</h1>

  @if(count($available_surveys) > 0)
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
              {{ Html::linkRoute('public_survey', 'Take this survey', [$available_survey->uuid], ['class' => 'btn btn-xs btn-primary']) }}
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  @endif
@endsection

