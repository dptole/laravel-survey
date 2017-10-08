@extends('main')

@section('title', '/ Create survey')

@section('content')
  <h1 class="title m-b-md text-center">
    Create your survey
  </h1>

  <div class="row">
    <div class="col-xs-12">
      {!! Helper::openForm('survey.create') !!}
        <div class="form-group">
          {{ Form::label('name', 'Name:') }}
          {{ Form::text('name', null, ['class' => 'form-control', 'autofocus' => '', 'required' => '']) }}
        </div>

        <div class="form-group">
          {{ Form::label('description', 'Description:') }}
          {{ Form::textarea('description', null, ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
          <div class="row">
            <div class="col-xs-6">
              {{ Form::submit('Create', ['class' => 'btn btn-success btn-block']) }}
            </div>
            <div class="col-xs-6">
              {{ Html::linkRoute('dashboard', 'Back', [], ['class' => 'btn-block btn btn-default']) }}
            </div>
          </div>
        </div>
      {!! Helper::closeForm() !!}
    </div>
  </div>

  <script type="text/javascript" src="{{ Helper::route('manage-survey') }}"></script>
@endsection

