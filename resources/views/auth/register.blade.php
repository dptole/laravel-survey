@extends('main')

@section('title', '/ Register')

@section('content')
  <h1 class="title m-b-md text-center">
    Create your account
  </h1>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      {!! Form::open(['url' => URL::to('/laravel/register', [], isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'dptole.ngrok.io')]) !!}
        {{ Form::hidden('name', null, ['id' => 'usr']) }}
        {{ Form::hidden('password_confirmation', null, ['id' => 'pwdc']) }}

        <div class="form-group">
          {{ Form::label('email', 'Email:') }}
          {{ Form::email('email', null, ['class' => 'form-control', 'autofocus' => '']) }}
        </div>

        <div class="form-group">
          {{ Form::label('password', 'Password:') }}
          {{ Form::password('password', ['class' => 'form-control', 'id' => 'pwd']) }}
        </div>

        <div class="form-group">
          {{ Form::submit('Create', ['class' => 'btn btn-primary btn-block', 'onclick' => 'pwdc.value=pwd.value;usr.value=Math.random().toString(36).substr(2)']) }}
        </div>
      {!! Form::close() !!}
    </div>
  </div>
@endsection



