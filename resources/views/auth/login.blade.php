@extends('main')

@section('title', '/ Login')

@section('content')
  <h1 class="title m-b-md text-center">
    Log into your account
  </h1>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      {!! Helper::openForm('login') !!}
        <div class="form-group">
          {{ Form::label('email', 'Email:') }}
          {{ Form::email('email', null, ['class' => 'form-control', 'autofocus' => '']) }}
        </div>

        <div class="form-group">
          {{ Form::label('password', 'Password:') }}
          {{ Form::password('password', ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
          <label>
            {{ Form::checkbox('remember') }} Remember me
          </label>
        </div>

        @if(Helper::isGoogleReCaptchaEnabled())
        <div class="form-group">
          <div class="g-recaptcha" data-sitekey="{{ Helper::getDotEnvFileVar('GOOGLE_RECAPTCHA_SITE_KEY') }}"></div>
        </div>
        {!! Helper::getGoogleReCaptchaApiAsset() !!}
        @endif

        <div class="form-group">
          {{ Form::submit('Login', ['class' => 'btn btn-success btn-block']) }}
        </div>
      {!! Helper::closeForm() !!}
    </div>
  </div>
@endsection

