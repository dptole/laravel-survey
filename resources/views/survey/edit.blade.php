@extends('main')

@section('title', '/ Edit survey')

@section('content')
  <h1 class="title m-b-md text-center">
    Edit your survey
  </h1>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      {!! Form::model($survey, ['url' => URL::to('/laravel/dashboard/survey/' . $survey->uuid . '/edit', [], isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'dptole.ngrok.io')]) !!}
        <div class="form-group">
          {{ Form::label('name', 'Name:') }}
          {{ Form::text('name', $survey->name, ['class' => 'form-control', 'requried' => '', 'autofocus' => '']) }}
        </div>

        <div class="form-group">
          {{ Form::label('description', 'Description:') }}
          {{ Form::textarea('description', $survey->description, ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
          <h1 class="title text-center">Questions</h1>
        </div>

        <div class="form-group">
          <table class="table table-bordered{{ count($questions) > 0 ? ' table-hover' : '' }}">
            <thead>
              <tr>
                <th class="hidden-xs">UUID</th>
                <th>Question</th>
                <th class="hidden-xs">Last edited</th>
                <th class="hidden-xs"></th>
              </tr>
            </thead>
            <tbody>
              @if(count($questions) === 0)
                <tr>
                  <td colspan="4" class="survey-first-question-line">
                    <h3 class="text-center">
                      {{ Html::linkRoute('question.create', 'Create', [$survey->uuid], ['class' => 'survey-btn-first-question btn btn-primary']) }} your first question.
                    </h3>
                  </td>
                </tr>
              @else
                @foreach($questions as $question)

                @endforeach
                <tr>
                  <td colspan="4" class="survey-first-question-line">
                    <h3 class="text-center">
                      {{ Html::linkRoute('question.create', 'Create', [$survey->uuid], ['class' => 'survey-btn-another-question btn btn-primary']) }} another question.
                    </h3>
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>

        <div class="form-group">
          <div class="row">
            <div class="col-xs-6">
              {{ Form::submit('Update', ['class' => 'btn btn-block btn-success']) }}
            </div>
            <div class="col-xs-6">
              {{ Html::linkRoute('dashboard', 'Back', [], ['class' => 'btn-block btn btn-default']) }}
            </div>
          </div>
        </div>
      {!! Form::close() !!}
    </div>
  </div>
@endsection

