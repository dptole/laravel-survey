@extends('layouts.app')

@section('title', 'Home')

@section('content')

  <h1 class="text-center">GCU Teachers Feedback Survey List!</h1>

  @if(count($available_surveys) > 0)
      <div class="row">
        <div class="col-md-12">
            <div class="card">
                
                <div class="card-body py-3">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Survey</th>
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
                                      {{ Html::linkRoute('public_survey.show', 'Start this survey', [$available_survey->uuid], ['class' => 'btn btn-xs btn-primary']) }}
                                    </div>
                                  </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
  @endif
@endsection