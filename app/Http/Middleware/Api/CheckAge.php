<?php

namespace App\Http\Middleware\Api;

use Closure;

class CheckAge
{
    public function handle($request, Closure $next)
    {
        if($request->age < 18){
            return response()->json(['message' => "You must be over 18 years old to use this API.",
            'code' => 401], 401);
        }
        return $next($request);
    }
}
