<form method="POST" action="{{ route('course.store') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
   
    <div class="form-group">
        <label class="col-form-label">Course Code</label>
        <input type="text" name="course_code" class="form-control" value="{{ old('course_code') }}" required>
        @if ($errors->has('course_code'))
            <span class="text-danger text-sm">
                {{ $errors->first('course_code') }}
            </span>
        @endif
    </div>

    <div class="form-group">
        <label class="col-form-label">Course Title</label>
        <input type="text" name="course_title" class="form-control" value="{{ old('course_title') }}" required>
        @if ($errors->has('course_title'))
            <span class="text-danger text-sm">
                {{ $errors->first('course_title') }}
            </span>
        @endif
    </div>

    <div class="form-group">
        <label class="col-form-label">Semester</label>
        <select class="form-control" name="semester" id="semester">
                    <option disabled="true" selected="true">Chose One...</option>
                    <option value=1>{{ "Semester 1"}}</option>
                    <option value=2>{{ "Semester 2"}}</option>
                    <option value=3>{{ "Semester 3"}}</option>
                    <option value=4>{{ "Semester 4"}}</option>
        </select>
  </div>

  <div class="form-group">
        <label class="col-form-label">Program</label>
        <select class="form-control" name="program" id="program">
                    <option disabled="true" selected="true">Chose One...</option>
            @foreach($programs as $program)
                <option value="{!!$program->id!!}">{{ $program->name}}</option>
            @endforeach
        </select>
  </div>

    <!-- <div class="form-group">
        <label class="col-form-label">Department</label>
        <select class="form-control" name="department" id="department">
                    <option disabled="true" selected="true">Chose One...</option>
            @foreach($departments as $department)
                <option value="{!!$department->id!!}">{{ $department->name}}</option>
            @endforeach
        </select>
  </div> -->

    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Add">
    </div>
</form>