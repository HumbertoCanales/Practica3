<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Access\AuthorizationException;
use App\User;

class VerificationController extends Controller
{
    use VerifiesEmails;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        //$this->middleware('auth:sanctum')->only('resend');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(Request $request)
    {
        $userId = $request['id'];
        $user = User::findOrFail($userId);
        if ($request->route('id')!= $user->id) {
            throw new AuthorizationException;
        }
        if ((string)$request->route('hash') != sha1($user->email)) {
            throw new AuthorizationException;
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'You are already verified']);
        }
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        if ($response = $this->verified($request)) {
            return $response;
        }
        $date = date('Y-m-d g:i:s');
        $user->email_verified_at = $date;
        $user->save();
        return response()->json(['message' => 'Successfully verified']);
    }

    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'You are already verified']);
        }

        $user->sendApiEmailVerificationNotification();

        return $request->wantsJson()
                    ? response()->json(['message' => 'Verification email resent'])
                    : back()->with('resent', true);
    }
}
