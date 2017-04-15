<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Users;

class RegisterController extends Controller {
  public function index() {
    return view('register');
  }

  public function store(Request $request) {
    $this->validate($request, [
      'password' => 'required|min:8',
      'email' => 'required|email'
    ]);

    $user = new Users;
    $user->email = $request->input('email');
    $user->password = Hash::make($request->input('password'));
    $user->active = '1';
    $user->save();
    $request->session()->flash('success', 'User created!');

    return redirect()->route('register.index');
  }
}

