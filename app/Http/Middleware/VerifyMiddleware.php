<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class VerifyMiddleware
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
        $facebookVerification = 'mezzohotel';
        if ($request->input("hub_mode") === "subscribe" && $request->input("hub_verify_token") === $facebookVerification){
            return response($request->input("hub_challenge"), 200);
        }
        return $next($request);
    }
}
