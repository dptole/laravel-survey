<!doctype html>
<html lang="{{ config('app.locale') }}">
  <head>
    @include('partials.header')
  </head>
  <body>
    @include('partials.nav')
    <div class="container">
      @include('partials.messages')
      @yield('content')
    </div>
    @include('partials.javascript')
    @include('partials.footer')
    <script>@yield('javascript')</script>
  </body>
</html>

