@extends('main')

@section('title', '/ Register')

@section('content')
  <h1 class="title m-b-md text-center">
    Create your account
  </h1>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      {!! Form::open(['url' => URL::to('/laravel/register', [], isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'dptole.ngrok.io')]) !!}
        <div class="form-group">
          {{ Form::label('email', 'Email:') }}
          {{ Form::email('email', null, ['class' => 'form-control', 'autofocus' => '']) }}
        </div>

        <div class="form-group">
          {{ Form::label('password', 'Password:') }}
          {{ Form::password('password', ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
          {{ Form::submit('Create', ['class' => 'btn btn-primary btn-block']) }}
        </div>
      {!! Form::close() !!}
    </div>
  </div>
@endsection

