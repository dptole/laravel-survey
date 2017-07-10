@extends('main')

@section('title', '/ Edit survey')

@section('content')
  <h1 class="title m-b-md text-center">
    Edit your survey
  </h1>

  <div class="row">
    <div class="col-xs-12">
      {!! Helper::openForm('survey.edit', [$survey->uuid]) !!}
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
                <th>Last edited</th>
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
                @foreach($questions as $index => $question)
                  <tr>
                    <td class="hidden-xs">{{ $question->uuid }}</td>
                    <td>
                      {{ $question->description }}
                      <div class="visible-xs">
                        {{ Html::linkRoute('question.edit', 'Edit', [$survey->uuid, $question->uuid], ['class' => 'survey-question-edit btn btn-warning btn-xs']) }}
                        {{ Html::linkRoute('question.delete', 'Delete', [$survey->uuid, $question->uuid], ['class' => 'survey-question-delete btn btn-danger btn-xs']) }}
                      </div>
                    </td>
                    <td>{{ date('c', strtotime($question->updated_at)) }}</td>
                    <td class="hidden-xs">
                      @if(!$survey->is_running)
                      {{ Html::linkRoute('question.edit', 'Edit', [$survey->uuid, $question->uuid], ['class' => 'survey-question-edit btn btn-warning btn-xs']) }}
                      {{ Html::linkRoute('question.delete', 'Delete', [$survey->uuid, $question->uuid], ['class' => 'survey-question-delete btn btn-danger btn-xs']) }}
                      @endif
                    </td>
                  </tr>
                @endforeach

                @if(!$survey->is_running)
                <tr>
                  <td colspan="4" class="survey-first-question-line">
                    <h3 class="text-center">
                      {{ Html::linkRoute('question.create', 'Create', [$survey->uuid], ['class' => 'survey-btn-another-question btn btn-primary']) }} another question.
                    </h3>
                  </td>
                </tr>
                @endif
              @endif
            </tbody>
          </table>
        </div>

        <div class="text-center">
          {!! $questions->links() !!}
        </div>

        <div class="form-group">
          <div class="row">
            <div class="col-sm-4 col-xs-12">
              @if(!$survey->is_running)
              {{ Form::submit('Update', ['class' => 'btn btn-block btn-success']) }}
              @else
              {{ Html::linkRoute('public_survey', 'Start this survey', [$survey->uuid], ['class' => 'btn btn-block btn-primary']) }}
              @endif
            </div>
            <div class="col-sm-4 col-xs-12">
              @if($survey->status === 'draft')
                {{ Html::linkRoute('survey.run', 'Run', [$survey->uuid], ['class' => 'btn btn-block btn-primary']) }}
              @elseif($survey->status === 'ready')
                {{ Html::linkRoute('survey.pause', 'Pause', [$survey->uuid], ['class' => 'btn btn-block btn-danger']) }}
              @endif
            </div>
            <div class="col-sm-4 col-xs-12">
              {{ Html::linkRoute('dashboard', 'Back', [], ['class' => 'btn-block btn btn-default']) }}
            </div>
          </div>
        </div>
      {!! Helper::closeForm() !!}
    </div>
  </div>
@endsection

