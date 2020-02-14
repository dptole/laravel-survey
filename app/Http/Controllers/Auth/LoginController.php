<?php

namespace App\Http\Controllers\Auth;

use App\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
    validateLogin as parentValidateLogin;
  }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        return redirect()->route('dashboard');
    }

    // https://stackoverflow.com/a/40887817
    protected function logout(Request $request)
    {
        $user = \Auth::user();

        $farewell = $user
        ? 'See you later '.$user->name.'!'
        : 'See you later!';

        $this->performLogout($request);

        $request->session()->flash('success', $farewell);

        return redirect()->route('home');
    }

    protected function validateLogin(Request $request)
    {
        $this->parentValidateLogin($request);

        if (!Helper::isGoogleReCaptchaEnabled()) {
            return;
        }

        $rule = [
            'g-recaptcha-response' => 'required|google_recaptcha',
        ];

        $mocked_response = Helper::getTestEnvMockVar('googleReCaptchaFailed', 0);

        if ($mocked_response === true) {
            Validator::make([], $rule)->validate();
        } elseif ($mocked_response === false) {
            Validator::make([], [])->validate();
        }

        Validator::make($request->all(), $rule)->validate();
    }
}
