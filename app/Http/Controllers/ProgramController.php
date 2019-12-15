<?php

namespace App\Http\Controllers;

use App\Program;
use App\Department;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    
    public function index()
    {
        $programs = Program::paginate(25);
        $departments = Department::all();

        return view('program.index', compact('programs','departments'));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'program_name'  => 'required|string',
            'department' => 'required'
        ]);
        
        $program = new Program;
        $program->name = $request['program_name'];
        $program->department_id = $request['department'];
        $program->save();

        // flash('Program Added!')->success()->important();

        return redirect()->route('program.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function show(Program $program)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function edit(Program $program)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Program $program)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function destroy(Program $program, int $id)
    {
        // dd($id);
        // flash('Department is deleted!')->important();
        Program::find($id)->delete();

        return redirect()->route('program.index');
    }
}
