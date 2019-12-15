<?php

namespace App\Http\Controllers;

use App\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{

    public function index()
    {
        $departments = Department::paginate(25);
        return view('department.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'name' => 'required|string|min:3|max:255|unique:departments',
        ]);

        $department = new Department;
        $department->name = $request['name'];
        $department->save();

        // flash('Department added.');

        return redirect()->route('department.index');
    }

    public function destroy(Department $department, int $id)
    {
        // dd($id);
        // flash('Department is deleted!')->important();
        Department::find($id)->delete();

        return redirect()->route('department.index');
    }
}
