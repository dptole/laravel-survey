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
          <table class="table table-bordered{{ count($questions) > 0 ? ' table-hover' : ''}}">
            <thead>
              <tr>
                <th class="hidden-xs">UUID</th>
                <th>Question</th>
                <th class="hidden-xs">Last edited</th>
                <th class="hidden-xs"></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="4">
                  <h3 class="text-center">
                    {{ Html::link('javascript:0', 'Create', ['class' => 'survey-btn-first-question btn btn-primary']) }} your first question.
                  </h3>
                </td>
              </tr>
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

