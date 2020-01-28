<?php

namespace App\Http\Middleware;

use Closure;
use App\Helper;

class SetupCheck
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
        if(Helper::hasPendingDotEnvFileConfigs()):
          $route = $request->route();
          $ok_routes = [
            'setup-update-missing-configs',
            'fonts',
            'js',
            'css',
            'home'
          ];
          if(!in_array($route->getName(), $ok_routes, true)):
            $request->session()->flash('warning', 'The system can\'t function without the correct parameters.');
            return redirect('home');
          endif;
        endif;

        return $next($request);
    }
}
