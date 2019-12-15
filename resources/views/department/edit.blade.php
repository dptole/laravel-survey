@extends('layouts.app')

@section('title', 'Edit Course')

@section('head')

    <!-- include summernote css/js-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.11/summernote-bs4.css" rel="stylesheet">

    <style type="text/css">
        body{
            /*margin: 0;*/
            font-family: Roboto;
            /*font-size: 0.95rem;*/
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #ebebeb;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('font-awesome-4.7.0/css/font-awesome.min.css')}}">

@endsection


@section('content')
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span>Course Form</span>
        </div>
        <form method="POST" action="{{ route('course.update', $course->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div>Course Name</div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="col-form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $course->name }}">
                        </div>
                    </div>
                </div>
            </div>

            <hr/>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div>Course Files</div>
                    </div>
                    <div class="col-md-8">
                        @if(!isset($course->programme_content))
                            <div class="form-group">
                                <label class="col-form-label">Programme PDF</label>
                                <input type="file" name="programme_pdf" class="form-control-file">
                            </div>
                        @else
                            <div class="form-group">
                                <label class="col-form-label">Programme PDF: </label>
                                <label class="fileinput-filename" style="color: red"><?=$course->programme_content;?></label><a href="{{ route('course.programme', $course->id) }}" style="color: red"><i class="fa fa-times" aria-hidden="true"></i></a>
                                <input type="file" name="programme_pdf" class="form-control-file">
                            </div>
                        @endif

                        @if(!isset($course->documentation_and_identification))
                            <div class="form-group">
                                <label class="col-form-label">Documentation & Identification PDF</label>
                                <input type="file" name="identification_pdf" class="form-control-file">
                            </div>
                        @else
                            <div class="form-group">
                                <label class="col-form-label">Documentation & Identification PDF: </label>
                                <label class="fileinput-filename" style="color: red"><?=$course->documentation_and_identification?></label>
                                <a href="{{ route('course.documentation', $course->id) }}" style="color: red"><i class="fa fa-times" aria-hidden="true"></i></a>
                                <input type="file" name="identification_pdf" class="form-control-file">
                            </div>
                        @endif

                        @if(!isset($course->math_test_preparation))
                            <div class="form-group">
                                <label class="col-form-label">Maths Test preparation PDF</label>
                                <input type="file" name="mathtest_pdf" class="form-control-file">
                            </div>
                        @else
                            <div class="form-group">
                                <label class="col-form-label">Maths Test preparation PDF: </label>
                                <label class="fileinput-filename" style="color: red"><?=$course->math_test_preparation ?></label><a href="{{ route('course.maths', $course->id) }}" style="color: red"><i class="fa fa-times" aria-hidden="true"></i></a>
                                <input type="file" name="mathtest_pdf" class="form-control-file">
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <hr/>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div>Course Content</div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="col-form-label">Dress Code</label>
                            <textarea name="dress_code" class="form-control summernote" rows="150" placeholder ="Define Dress Code for {{ $course->name }}">{{ $course->dress_code }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Presentation</label>
                            <textarea name="presentation" class="form-control summernote" rows="150" placeholder ="Define Presentation for {{ $course->name }}">{{ $course->presentation }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Qualifications</label>
                            <textarea name="qualification" class="form-control summernote" rows="50" placeholder ="Define Qualifications for {{ $course->name }}">{{ $course->qualification }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body form-group float-right">
                <a href="{{ route('course.index') }}" name="skip" id="skip" class="btn btn-info">Skip</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
@endsection


@push('scripts')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.11/summernote-bs4.js"></script>
    <script>
        // Summernote Height
        // $('.summernote').summernote({
        //     height: 150,   //set editable area's height
        // });

        $(document).ready(function() {
            $('.summernote').summernote({
              toolbar: [
                // [groupName, [list of button]]
                ['style',['style']],
                // ['font',['font']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname',['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table',],
                ['hr',],
                ['height', ['height']],
                ['link',],

              ],

              height: 150,
            });
        });

    </script>

@endpush