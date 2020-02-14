<?php

namespace App\Http\Middleware;

use Closure;

class EmailCheckDNSRR
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Validator::extend('email_checkdnsrr', function ($attribute, $value, $parameters, $validator) {
            $at_index = strpos($value, '@');
            if (!~$at_index) {
                return false;
            }
            $host = substr($value, 1 + $at_index);

            return checkdnsrr($host);
        }, 'This email address points to an invalid domain.');

        return $next($request);
    }
}
