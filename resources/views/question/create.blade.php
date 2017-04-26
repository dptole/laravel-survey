@extends('main')

@section('title', '/ Create question')

@section('content')
  <h1 class="title m-b-md text-center">
    Create your question
  </h1>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      {!! Form::open(['data-survey-uuid' => $survey->uuid, 'id' => 'survey-form-question', 'url' => URL::to('/laravel/dashboard/survey/' . $survey->uuid . '/question/create', [], isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'dptole.ngrok.io')]) !!}
        <div class="form-group">
          {{ Form::label('description', 'Description:') }}
          {{ Form::textarea('description', null, ['class' => 'form-control', 'autofocus' => '', 'required' => '']) }}
        </div>

        <div class="form-group">
          <table class="table table-bordered table-hover survey-answers-table">
            <thead>
              <tr>
                <th>Possible answers</th>
                <th>
                  {{ Html::link('#', 'Add answer', ['class' => 'btn btn-success pull-right survey-add-answer btn-xs']) }}
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>

        <div class="form-group">
          <div class="row">
            <div class="col-xs-6">
              {{ Form::submit('Create', ['class' => 'btn btn-success btn-block']) }}
            </div>

            <div class="col-xs-6">
              {{ Html::linkRoute('survey.edit', 'Back', [$survey->uuid], ['class' => 'btn-block btn btn-default']) }}
            </div>
          </div>
        </div>
      {!! Form::close() !!}
    </div>
  </div>
@endsection

