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
    @include('partials.footer')
  </body>
</html>

