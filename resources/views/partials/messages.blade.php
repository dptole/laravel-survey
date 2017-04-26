@if (Session::has('success'))
  <div class="alert alert-success">
    <strong>Success:</strong> {{ Session::get('success') }}
  </div>
@endif

@if (Session::has('warning'))
  <div class="alert alert-warning">
    <strong>Warning:</strong> {{ Session::get('warning') }}
  </div>
@endif

@if (count($errors) > 0)
  <div class="alert alert-danger survey-errors">
    <strong>Errors:</strong>
    <ul>
    @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach  
    </ul>
  </div>
@endif

