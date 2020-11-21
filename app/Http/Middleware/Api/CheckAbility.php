<?php

namespace App\Http\Middleware\Api;

use App\User;
use Closure;
use App\Mail\UnauthorizedRequest;
use Illuminate\Support\Facades\Mail;

class CheckAbility
{
    public function handle($request, Closure $next, $ability)
    {
        if(!$request->user()->tokenCan('admin:admin')){
            if(!$request->user()->tokenCan($ability)){
                $admins = User::whereHas('abilities', function($q) {
                    $q->where('name','like','admin:admin');
                })->get();
                foreach ($admins as $admin) {
                    Mail::to($admin)->send(new UnauthorizedRequest($request->user()->name, $request->user()->id, $ability));
                }
                return response()->json(['message' => "Unauthorized"], 401);
            }
        }
        return $next($request);
    }
}
