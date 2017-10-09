@extends('main')

@section('title', '/ Register')

@section('content')
  <h1 class="title m-b-md text-center">
    Create your account
  </h1>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      {!! Helper::openForm('register') !!}
        {{ Form::hidden('password_confirmation', null, ['id' => 'pwdc']) }}

        <div class="form-group">
          {{ Form::label('name', 'Name:') }}
          {{ Form::text('name', null, ['class' => 'form-control', 'autofocus' => '']) }}
        </div>

        <div class="form-group">
          {{ Form::label('email', 'Email:') }}
          {{ Form::email('email', null, ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
          {{ Form::label('password', 'Password:') }}
          {{ Form::password('password', ['class' => 'form-control', 'id' => 'pwd']) }}
        </div>

        <div class="form-group">
          <div class="g-recaptcha" data-sitekey="{{ env('GOOGLE_RECAPTCHA_SITE_KEY') }}"></div>
        </div>

        <div class="form-group">
          {{ Form::submit('Create', ['class' => 'btn btn-success btn-block', 'onclick' => 'pwdc.value=pwd.value;usr.value=Math.random().toString(36).substr(2)']) }}
        </div>
      {!! Helper::closeForm() !!}
    </div>
  </div>
@endsection



