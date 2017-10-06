@extends('main')

@section('title', '/ Edit question')

@section('content')
  <h1 class="title m-b-md text-center">
    Edit your question
  </h1>

  <div class="row">
    <div class="col-xs-12">
      {!! Helper::openForm('question.edit', [$survey->uuid, $question->uuid], ['autocomplete' => 'off', 'data-survey-uuid' => $survey->uuid, 'data-question-options' => json_encode($question_options), 'id' => 'survey-form-question']) !!}
        <div class="form-group">
          {{ Form::label('description', 'Description:') }}
          {{ Form::textarea('description', $question->description, ['class' => 'form-control', 'autofocus' => '', 'required' => '']) }}
        </div>

        <div class="form-group">
          <table class="table table-bordered table-hover survey-answers-table">
            <thead>
              <tr>
                <th>#</th>
                <th>
                  Possible answers
                  {{ Html::link('#', 'Add answer', ['class' => 'btn btn-success pull-right survey-add-answer btn-xs']) }}
                </th>
                <th>
                  <div class="pull-right">
                    {{ Html::link('#', 'Up', ['class' => 'btn btn-default survey-order-up-answer btn-xs']) }}
                    {{ Html::link('#', 'Down', ['class' => 'btn btn-default survey-order-down-answer btn-xs']) }}
                    {{ Html::link('#', 'Change order', ['class' => 'btn btn-default survey-order-answer btn-xs']) }}
                  </div>
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
      {!! Helper::closeForm() !!}
    </div>
  </div>

  <script type="text/javascript" src="https://dptole.ngrok.io/laravel/r/questions.js"></script>
@endsection

