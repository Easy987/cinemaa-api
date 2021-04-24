<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->has('lang')) {
            $lang = $request->get('lang');
            if(in_array($lang, config('cinema.allowed_langs'), true)) {
                Carbon::setLocale($lang);
                App::setLocale($lang);
            }
        }
        return $next($request);
    }
}
