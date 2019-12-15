<form method="POST" action="{{ route('teacher.store') }}">
    
    {{-- @csrf --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div class="form-group">
        <label class="col-form-label">Full Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        @if ($errors->has('name'))
            <span class="text-danger text-sm">
                {{ $errors->first('name') }}
            </span>
        @endif
    </div>

    <div class="form-group">
        <label class="col-form-label">Designation</label>
        <select class="form-control" name="designation" id="designation">
            <option disabled="true" selected="true">Chose One...</option>
            <option value="visiting lecturer">Visiting Lecturer</option>
            <option value="lecturer">Lecturer</option>            
        </select>
    </div>

    <div class="form-group">
        <label class="col-form-label">Email</label>
        <input type="text" name="email" class="form-control" value="{{ old('email') }}">
        @if ($errors->has('email'))
            <span class="text-danger text-sm">
                {{ $errors->first('email') }}
            </span>
        @endif
    </div>

    <div class="form-group">
        <label class="col-form-label">Mobile Number</label>
        <input type="text" name="mobile_number" class="form-control" value="{{ old('mobile_number') }}">
        @if ($errors->has('mobile_number'))
            <span class="text-danger text-sm">
                {{ $errors->first('mobile_number') }}
            </span>
        @endif
    </div>

    <div class="form-group">
        <label class="col-form-label">Phone Number</label>
        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}">
        @if ($errors->has('phone_number'))
            <span class="text-danger text-sm">
                {{ $errors->first('phone_number') }}
            </span>
        @endif
    </div>

    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Add">
    </div>
</form>


@push('scripts')

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.1.62/jquery.inputmask.bundle.js"></script>

<script type="text/javascript">
   
   $("#ucas").inputmask({'mask': '999-999-9999'});

</script>


@endpush