
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Laravel @yield('title')</title>

<script>
window.Laravel = {csrfToken: '{{ csrf_token() }}'};

window.LARAVEL_SURVEY_PREFIX_URL = '{{ env('LARAVEL_SURVEY_PREFIX_URL') }}';

window.PUSHER_ENABLED = {{ env('PUSHER_ENABLED') ? 'true' : 'false' }};
window.PUSHER_APP_KEY = '{{ env('PUSHER_APP_KEY') }}';
window.PUSHER_APP_CLUSTER = '{{ env('PUSHER_APP_CLUSTER') }}';

window.GOOGLE_RECAPTCHA_ENABLED = {{ env('GOOGLE_RECAPTCHA_ENABLED') ? 'true' : 'false' }};
</script>
<link href="https://getbootstrap.com/docs/3.3/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<link rel="icon" type="image/x-icon" href="https://avatars2.githubusercontent.com/u/3951114">
<link href="{{ Helper::route('css') }}" rel="stylesheet">
