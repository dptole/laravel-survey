@extends('main')

@section('title', '/ Login')

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <h1>Login</h1>

        {!! Form::open() !!}

          {{ Form::label('email', 'Email:') }}
          {{ Form::email('email', null, ['class' => 'form-control']) }}

          {{ Form::label('password', 'Password:') }}
          {{ Form::password('password', ['class' => 'form-control']) }}

        {!! Form::close() !!}
      </div>
    </div>
  </div>
@endsection

