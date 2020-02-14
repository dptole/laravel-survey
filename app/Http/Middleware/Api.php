<?php

namespace App\Http\Middleware;

use App\ApiErrors;
use App\Helper;
use Closure;

class Api
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
        $response = $next($request);

        $status_code = $response->original instanceof ApiErrors ? $response->original->status : $response->getStatusCode();
        $response_type = Helper::isSuccessHTTPStatus($status_code) ? 'success' : 'error';

        return response(json_encode([$response_type => $response->original]), $status_code)
          ->header('content-type', 'application/json;charset=utf-8');
    }
}
