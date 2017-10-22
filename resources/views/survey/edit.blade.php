@extends('main')

@section('title', '/ Edit survey')

@section('content')
  <h1 class="title m-b-md text-center">
    Edit your survey
  </h1>

  <div class="row">
    <div class="col-xs-12">
      {!! Helper::openForm('survey.edit', [$survey->uuid], ['autocomplete' => 'off']) !!}
        <div class="form-group">
          {{ Form::label('name', 'Name:') }}
          {{ Form::text('name', $survey->name, ['class' => 'form-control', 'requried' => '', ($survey->is_running ? 'disabled' : 'non-disabled') => 'true', 'autofocus' => '']) }}
        </div>

        @if($survey->is_running)
        <div class="form-group">
          {{ Form::label('shareable_link', 'Shareable link:') }}
          {{ Html::link($survey->shareable_url, $survey->shareable_url, ['class' => 'text-ellipsis']) }}
        </div>
        @endif

        <div class="form-group">
          {{ Form::label('description', 'Description:') }}
          {{ Form::textarea('description', $survey->description, ['class' => 'form-control', ($survey->is_running ? 'disabled' : 'non-disabled') => 'true']) }}
        </div>

        <div class="form-group">
          <h1 class="title text-center">Questions</h1>
        </div>

        <div class="form-group">
          <table class="table table-bordered{{ count($questions) > 0 ? ' table-hover' : '' }}">
            <thead>
              @if(count($questions) > 0)
              <tr>
                <th>#</th>
                <th class="hidden-xs">UUID</th>
                <th>
                  Question
                  <div class="pull-right">
                    @if(count($questions) > 1 && !$survey->is_running)
                      {{ Html::linkRoute('question.show_change_order', 'Change order', [$survey->uuid], ['class' => 'visible-xs btn btn-default btn-xs']) }}
                    @endif
                  </div>
                </th>
                <th class="hidden-xs">Last edited</th>

                @if(!$survey->is_running && count($questions) > 0)
                <th class="hidden-xs">
                  @if(count($questions) > 1)
                    {{ Html::linkRoute('question.show_change_order', 'Change order', [$survey->uuid], ['class' => 'btn btn-default btn-xs']) }}
                  @endif
                </th>
                @endif
              </tr>
              @endif
            </thead>
            <tbody>
              @if(count($questions) === 0)
                <tr>
                  <td class="survey-first-question-line">
                    <h3 class="text-center">
                      {{ Html::linkRoute('question.create', 'Create', [$survey->uuid], ['class' => 'survey-btn-first-question btn btn-primary']) }} your first question.
                    </h3>
                  </td>
                </tr>
              @else
                @foreach($questions as $index => $question)
                  <tr>
                    <td>{{ $question->order }}</td>
                    <td class="hidden-xs">{{ $question->uuid }}</td>
                    <td>
                      {{ $question->description }}
                      <div class="visible-xs">
                        @if(!$survey->is_running)
                          {{ Html::linkRoute('question.edit', 'Edit', [$survey->uuid, $question->uuid], ['class' => 'survey-question-edit btn btn-warning btn-xs']) }}
                          {{ Html::linkRoute('question.delete', 'Delete', [$survey->uuid, $question->uuid], ['class' => 'survey-question-delete btn btn-danger btn-xs']) }}
                        @endif
                      </div>
                    </td>
                    <td class="hidden-xs">
                      <span title="{{ $question->updated_at_rfc1123 }}">{{ $question->updated_at_diff }}</span>
                    </td>

                    @if(!$survey->is_running)
                    <td class="hidden-xs">
                      {{ Html::linkRoute('question.edit', 'Edit', [$survey->uuid, $question->uuid], ['class' => 'survey-question-edit btn btn-warning btn-xs']) }}
                      {{ Html::linkRoute('question.delete', 'Delete', [$survey->uuid, $question->uuid], ['class' => 'survey-question-delete btn btn-danger btn-xs']) }}
                    </td>
                    @endif
                  </tr>
                @endforeach

                @if(!$survey->is_running)
                <tr>
                  <td colspan="5" class="survey-first-question-line">
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

        <div class="row">
          <div class="col-sm-4 col-xs-12 form-group">
            @if(!$survey->is_running)
              {{ Form::submit('Update', ['class' => 'btn btn-block btn-success']) }}
            @else
              {{ Html::linkRoute('survey.stats', 'Stats', [$survey->uuid], ['class' => 'btn btn-info btn-block']) }}
            @endif
          </div>
          <div class="col-sm-4 col-xs-12 form-group">
            @if(count($questions) > 0)
              @if($survey->status === 'draft')
                {{ Html::linkRoute('survey.run', 'Run', [$survey->uuid], ['class' => 'btn btn-block btn-primary']) }}
              @elseif($survey->status === 'ready')
                {{ Html::linkRoute('survey.pause', 'Pause', [$survey->uuid], ['class' => 'btn btn-block btn-danger']) }}
              @endif
            @endif
          </div>
          <div class="col-sm-4 col-xs-12 form-group">
            {{ Html::linkRoute('dashboard', 'Back', [], ['class' => 'btn-block btn btn-default']) }}
          </div>
        </div>
      {!! Helper::closeForm() !!}
    </div>
  </div>
@endsection

