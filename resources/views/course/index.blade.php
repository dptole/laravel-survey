@extends('layouts.app')

@section('title', 'Courses')

@section('head')

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.4/css/buttons.dataTables.min.css">

@endsection

@section('content')

    <div class="collapse {{$errors->any() ? 'show': ''}}" id="collpaseCreateCourseForm">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Add Course</span>
                <span class="icon" id="jshideCreateCourseForm">
                    <svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <path d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"/>
                    </svg>
                </span>
            </div>
            <div class="card-body">
            @include('course._form')
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span>Course List</span>
            <a class="btn btn-primary" data-toggle="collapse" href="#collpaseCreateCourseForm" role="button" aria-expanded="false" aria-controls="collpaseCreateCourseForm">Add New Course</a>
        </div>

        {{-- @include('course.modal.modal') --}}

        <div class="card-body table-responsive">
            <!-- <table class="table card-table table-hover"> -->
            <table id="course-table" class="table table-striped table-bordered wrap" data-toggle="dataTable" data-form="deleteForm">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Code Title</th>
                    <th>Semester</th>
                    <th>Program</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($courses as $course)
                    <tr>
                        <td>{{ $course->id }}</td>
                        <td>{{ $course->course_code }}</td>
                        <td>{{ $course->course_title }}</td>
                        <td>{{ $course->semester }}</td>
                        <td>{{ $course->Program->name }}</td>
                        <td>{{ $course->Department->name }}</td>
                        <td>
                            {!! Form::model($course, ['method' => 'get', 'route' => ['course.edit', $course->id], 'class' =>'form-inline form-edit d-inline']) !!}
                            <a href="{{ route('course.edit', $course->id) }}">
                                <span class="icon" style="fill:#adb5bd">
                                    <svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <path d="M12.3 3.7l4 4L4 20H0v-4L12.3 3.7zm1.4-1.4L16 0l4 4-2.3 2.3-4-4z"/>
                                    </svg>
                                </span>
                            </a>
                            {!! Form::hidden('id', $course->id) !!}
                            {!! Form::close() !!}

                            {!! Form::model($course, ['method' => 'delete', 'route' => ['course.destroy', $course->id], 'class' =>'form-inline form-delete d-inline']) !!}
                            <a name ="delete_modal" href="{{ route('course.destroy', $course->id) }}" >
                                <span class="icon" style="fill:#adb5bd">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"/></svg>
                                </span>
                            </a>
                            {!! Form::hidden('id', $course->id) !!}
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection


@push('scripts')
    <script type="text/javascript">
        $("#jshideCreateCourseForm").on('click', function() {
            $('#collpaseCreateCourseForm').collapse('hide');
        });


        $('table[data-form="deleteForm"]').on('click', '.form-delete', function(e){
            e.preventDefault();
            var $form=$(this);
            $('#confirm').modal({ backdrop: 'static', keyboard: false })
                .on('click', '#delete-btn', function(){
                    $form.submit();
                });
        });
    </script>

    <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.4/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.4/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.4/js/buttons.print.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#course-table').DataTable( {
                dom: 'Bfrtip',
                buttons: [
                    'excelHtml5',
                    'pdfHtml5',
                    'print',
                ]
            } );
        } );
    </script>
@endpush
