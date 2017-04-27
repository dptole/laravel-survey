@extends('main')

@section('title', '/ Edit question')

@section('content')
  <h1 class="title m-b-md text-center">
    Edit your question
  </h1>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      {!! Form::open(['data-survey-uuid' => $survey->uuid, 'data-question-options' => json_encode($question_options), 'id' => 'survey-form-question', 'url' => URL::to('/laravel/dashboard/survey/' . $survey->uuid . '/question/' . $question->uuid . '/edit', [], isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'dptole.ngrok.io')]) !!}
        <div class="form-group">
          {{ Form::label('description', 'Description:') }}
          {{ Form::textarea('description', $question->description, ['class' => 'form-control', 'autofocus' => '', 'required' => '']) }}
        </div>

        <div class="form-group">
          <table class="table table-bordered table-hover survey-answers-table">
            <thead>
              <tr>
                <th>#</th>
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
              {{ Form::submit('Save', ['class' => 'btn btn-success btn-block']) }}
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

