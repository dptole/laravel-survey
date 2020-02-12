<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helper;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
      logout as performLogout;
    }

    // https://stackoverflow.com/a/40887817
    public function logout(Request $request) {
        $user = \Auth::user();

        $farewell = $user
            ? 'See you later ' . $user->name . '!'
            : 'See you later!'
        ;

        $this->performLogout($request);

        $request->session()->flash('success', $farewell);

        return redirect()->route('home');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function sendLoginResponse(Request $request) {
        if(!Helper::isGoogleReCaptchaEnabled()):
            return $this->goToDashboard();
        endif;

        $validator = Validator::make(
          $request->all(),
          [
              'g-recaptcha-response' => 'required|google_recaptcha'
          ]
        );

        $failed_to_validate = Helper::getTestEnvMockVar(
          'googleReCaptchaFailed',
          $validator->fails()
        );

        if($failed_to_validate):
            return $this->logout($request)->withErrors($validator)->withInput();
        endif;

        return $this->goToDashboard();
    }

    protected function goToDashboard() {
        return redirect()->route('dashboard');
    }
}
