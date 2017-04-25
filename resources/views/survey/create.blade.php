@extends('main')

@section('title', '/ Create survey')

@section('content')
  <h1 class="title m-b-md text-center">
    Create your survey
  </h1>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      {!! Form::open(['url' => URL::to('/laravel/dashboard/survey/create', [], isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'dptole.ngrok.io')]) !!}
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
      {!! Form::close() !!}
    </div>
  </div>
@endsection

