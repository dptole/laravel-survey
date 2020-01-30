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
        \Validator::extend('google_recaptcha', function($attribute, $value, $parameters, $validator) {
          return Helper::isValidReCaptchaToken(Helper::getDotEnvFileVar('GOOGLE_RECAPTCHA_SITE_SECRET'), $value);
        }, 'ReCaptcha error.');

        return $next($request);
    }
}
