<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class StoreReferrer
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
        $response = $next($request);

        if ($request->has('ref')) {
            $referrer = User::where('referral_token', $request->get('ref'))->first();
            if ($referrer) {
                session(['referrer' => $referrer->id]);
            }
        }

        return $response;
    }
}
