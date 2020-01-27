<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

define('LOGIN_REDIRECT_TO', env('LARAVEL_SURVEY_PREFIX_URL') . '/dashboard');

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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = LOGIN_REDIRECT_TO;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = []) {
        return parent::validate(
            $request,
            array_merge(
                $rules,
                [
                    'g-recaptcha-response' => 'required|google_recaptcha'
                ]
            ),
            $messages,
            $customAttributes
        );
    }
}
