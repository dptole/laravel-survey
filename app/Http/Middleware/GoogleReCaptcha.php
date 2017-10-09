<?php

namespace App\Http\Middleware;

use Closure;

class GoogleReCaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(isset($_POST['g-recaptcha-response'])):
          \Validator::extend('google_recaptcha', function($attribute, $value, $parameters, $validator) {
            return (new \ReCaptcha\ReCaptcha(env('GOOGLE_RECAPTCHA_SITE_SECRET')))->verify($value)->isSuccess();
          }, 'ReCaptcha error.');
        endif;

        $response = $next($request);
        return $response;
    }
}
