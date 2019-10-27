<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    @yield('head')

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container col-lg-10">
        <div id="mainHeader" class="py-3">
            <div class="row">
                <div class="col">
                    <h3 class="text-danger font-weight-bold mb-0" style="letter-spacing: 0.05em;">GCU Feedback Portal</h3>
                </div>
                @if(Auth::check())
                    <div class="col-auto">
                        <a class="btn btn-sm btn-primary    " href="{{ route('survey.create') }}">
                            {{ __('Create survey') }}
                        </a>
                        <span class="small text-muted">Logged in as {{Auth::user()->name}}</span>
                        <a class="btn btn-sm btn-secondary" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </form>
                    </div>
                @endif
                @if(!Auth::check())
                    <div class="col-auto">
                        <a class="btn btn-sm btn-secondary" href="{{ route('login') }}">Login</a>
                        <a class="btn btn-sm btn-secondary" href="{{ route('register') }}">Register</a>
                    </div>
                @endif
            </div>
        </div>

        @if(Auth::check())
            <main class="mt-4 mb-4">
                <div class="row">
                    <div class="col-md-2">
                        @include('layouts.sidebar')
                    </div>

                    <div class="col-md-10">
                        {{-- @include('flash::message') --}}
                        @yield('content')
                    </div>
                </div>
            </main>
        @endif

    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @include('partials.javascript')
    @include('partials.footer')

</body>
    
    <script type="text/javascript"  src="{{ mix('js/app.js') }}"></script>
    @stack('scripts')

</html>
