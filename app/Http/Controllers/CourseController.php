<?php

namespace App\Http\Controllers;

use App\Course;
use App\Program;
use App\Department;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $courses = Course::paginate(25);
        $departments = Department::all();
        $programs = Program::all();

        return view('course.index', compact('courses', 'departments', 'programs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);

        $data = $this->validate($request, [
            'course_code'  => 'required|string',
            'course_title' => 'required|string',
            'semester'      => 'required',
            'program'       => 'required'
        ]);
        
        $course = new Course;
        $course->course_code = $request['course_code'];
        $course->course_title = $request['course_title'];
        $course->semester = $request['semester'];
        $course->program_id = $request['program'];
        $course->department_id = 1;
        $course->save();

        // flash('Program Added!')->success()->important();

        return redirect()->route('course.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit(Course $course)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        //
    }
}
