@extends('auth.app')

@section('content')

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Create your account') }}</div>

                <div class="card-body">

                    <div class="text-center mb-3 ">
                      <img src="{{ asset('images/qecgcu.png')}}" class="rounded" alt="...">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-offset-2 col-md-12 col-md-offset-2">
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
                                    {{ Form::submit('Create', ['class' => 'btn btn-success btn-block', 'onclick' => 'pwdc.value=pwd.value']) }}
                                </div>
                            {!! Helper::closeForm() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



  
@endsection



