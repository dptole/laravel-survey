@extends('layouts.app')

@section('title', 'Departments')

@section('head')

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.4/css/buttons.dataTables.min.css">

@endsection

@section('content')

    <div class="collapse {{$errors->any() ? 'show': ''}}" id="collpaseCreateDepartmentForm">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Add Department</span>
                <span class="icon" id="jshideCreateDepartmentForm">
                    <svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <path d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"/>
                    </svg>
                </span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('department.store') }}">
                    <!-- @csrf -->
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <label class="col-form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        @if ($errors->has('name'))
                            <span class="text-danger text-sm">
                            {{ $errors->first('name') }}
                        </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Add">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span>Department List</span>
            <a class="btn btn-primary" data-toggle="collapse" href="#collpaseCreateDepartmentForm" role="button" aria-expanded="false" aria-controls="collpaseCreateDepartmentForm">Add New Department</a>
        </div>

        @include('department.modal.modal')

        <div class="card-body table-responsive">
            <!-- <table class="table card-table table-hover"> -->
            <table id="department-table" class="table table-striped table-bordered wrap" data-toggle="dataTable" data-form="deleteForm">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($departments as $department)
                    <tr>
                        <td>{{ $department->id }}</td>
                        <td>{{ $department->name }}</td>
                        <td>
                            {!! Form::model($department, ['method' => 'delete', 'route' => ['department.destroy', $department->id], 'class' =>'form-inline form-delete d-inline']) !!}
                            <a name ="delete_modal" href="{{ route('department.destroy', $department->id) }}" >
                                <span class="icon" style="fill:#adb5bd">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"/></svg>
                                </span>
                            </a>
                            {!! Form::hidden('id', $department->id) !!}
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
        $("#jshideCreateDepartmentForm").on('click', function() {
            $('#collpaseCreateDepartmentForm').collapse('hide');
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
            $('#department-table').DataTable( {
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
