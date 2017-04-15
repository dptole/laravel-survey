<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Users;

class LoginController extends Controller {
  public function index() {
    return view('login');
  }

  public function store(Request $request) {
    $user = Users::where([
      'email' => $request->input('email')
    ])->find(1);

    if($user && Hash::check($request->input('password'), $user->password)):
      $request->session()->flash('success', 'Nothing happened!');
    else:
      $request->session()->flash('warning', 'Wrong password!');
    endif;
      
    return redirect()->route('login.index');
  }
}

