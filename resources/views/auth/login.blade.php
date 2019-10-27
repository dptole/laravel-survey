@extends('auth.app')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">

                    <div class="text-center mb-3 ">
                      <img src="{{ asset('images/qecgcu.png')}}" class="rounded" alt="...">
                    </div>
                    
                    <form method="POST" action="{{ route('login') }}">
                        {{-- @csrf --}}
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection









{{-- Previous Login Form --}}

{{-- 
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

        <div class="form-group">
          <div class="g-recaptcha" data-sitekey="{{ env('GOOGLE_RECAPTCHA_SITE_KEY') }}"></div>
        </div>

        <div class="form-group">
          {{ Form::submit('Login', ['class' => 'btn btn-success btn-block']) }}
        </div>
      {!! Helper::closeForm() !!}
    </div>
  </div>
@endsection

 --}}