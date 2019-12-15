@extends('layouts.app')

@section('title', 'Teachers Listing')

@section('head')

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.4/css/buttons.dataTables.min.css">

@endsection

@section('content')

    <div class="collapse {{$errors->any() ? 'show': ''}}" id="collpaseTeacherForm">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Add Teacher</span>
                <span class="icon" id="jshideCreateTeacherForm">
                <svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <path d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"/>
                </svg>
            </span>
            </div>
            <div class="card-body">
                @include('teacher._form')
            </div>
        </div>
    </div>

    <div class="card mb-3"> 
        <div class="card-header d-flex justify-content-between">
            <span>Teachers List</span>
            <div class="row">                
                <a class="btn btn-primary mr-2" data-toggle="collapse" href="#collpaseTeacherForm" role="button" aria-expanded="false" aria-controls="collapseExample">Add</a>
                <a href="{{ '#' }}" class="btn btn-primary">Add List</a>
                <!-- <a href="#" class="btn btn-primary">Export</a> -->
            </div>
        </div>

        {{-- @include('teacher.modal.modal') --}}

        <div class="table-responsive card-body">
            <table id="applicant-table" class="table table-striped table-bordered wrap" data-toggle="dataTable" data-form="deleteForm">
                <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Designation</th>
                    <th>Email</th>
                    <th>Mobile No.</th>
                    <th>Phone No.</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($teachers as $teacher)
                    <tr>
                        <td>{{ $teacher->name }}</td>
                        <td>{{ $teacher->designation }}</td>
                        <td>{{ $teacher->email }}</td>
                        <td>{{ $teacher->mobile_no }}</td>
                        <td>{{ $teacher->phone_no }}</td>
                        <td>
                            @if($teacher['status'] == 1)
                                <span class="icon" style="fill:#228B22">
                            @else
                                <span class="icon" style="fill:#adb5bd">
                            @endif
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"/></svg>
                            </span>
                        </td>

                        
                        <td>
                            {!! Form::model($teacher, ['method' => 'get', 'route' => ['teacher.edit', $teacher->id], 'class' =>'form-inline form-edit d-inline']) !!}
                            <a href="{{ route('teacher.edit', $teacher->id) }}">
                                <span class="icon" style="fill:#adb5bd">
                                    <svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <path d="M12.3 3.7l4 4L4 20H0v-4L12.3 3.7zm1.4-1.4L16 0l4 4-2.3 2.3-4-4z"/>
                                    </svg>
                                </span>
                            </a>
                            {!! Form::hidden('id', $teacher->id) !!}
                            {!! Form::close() !!}

                            {!! Form::model($teacher, ['method' => 'delete', 'route' => ['teacher.destroy', $teacher->id], 'class' =>'form-inline form-delete d-inline']) !!}
                            <a name ="delete_modal" href="{{ route('teacher.destroy', $teacher->id) }}" >
                                <span class="icon" style="fill:#adb5bd">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"/></svg>
                                </span>
                            </a>
                            {!! Form::hidden('id', $teacher->id) !!}
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
        $("#jshideCreateTeacherForm").on('click', function() {
            $('#collpaseTeacherForm').collapse('hide');
        });


        $('table[data-form="deleteForm"]').on('click', '.form-delete', function(e){
            e.preventDefault();
            var $form=$(this);
            $('#confirm').modal({ backdrop: 'static', keyboard: false })
                .on('click', '#delete-btn', function(){
                    $form.submit();
                });
        });

        console.log( $('#confirm'));
        
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
            $('#teacher-table').DataTable( {
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

    
