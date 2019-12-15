<form method="POST" action="{{ route('program.store') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
   
    <div class="form-group">
        <label class="col-form-label">Program Name</label>
        <input type="text" name="program_name" class="form-control" value="{{ old('program_name') }}" required>
        @if ($errors->has('program_name'))
            <span class="text-danger text-sm">
                {{ $errors->first('program_name') }}
            </span>
        @endif
    </div>

    <div class="form-group">
    <label class="col-form-label">Department</label>
    <select class="form-control" name="department" id="department">
        <option disabled="true" selected="true">Chose One...</option>
        @foreach($departments as $department)
            <option value="{!!$department->id!!}">{{ $department->name}}</option>
        @endforeach
    </select>
  </div>

    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Add">
    </div>
</form>