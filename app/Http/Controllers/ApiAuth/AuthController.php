<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\User;
use App\Ability;

class AuthController extends Controller
{
    public function signUp(Request $request){
        $request -> validate([
            'email' => 'email|required|unique:users',
            'name' => 'required|min:1|max:30',
            'age' => 'required|max:999',
            'password' => 'required|min:6'
        ]);
        $user = User::create([
            'email' => $request->email,
            'name' => $request->name,
            'age' => $request->age,
            'image' => 'prueba',
            'password' => Hash::make($request->password)]);
        if($user){
            $user->sendApiEmailVerificationNotification();
            $user->abilities()->attach(Ability::where('name','user:profile')->first());
            $user->abilities()->attach(Ability::where('name','post:publish')->first());
            $user->abilities()->attach(Ability::where('name','com:publish')->first());
            return response()->json($user, 201);
        }
        return abort(400, "Something went wrong...");
    }

    public function logIn(Request $request){
        $request -> validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);
        $user = User::where('email', $request->email)->first();
        if(!$user||!Hash::check($request->password, $user->password)){
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $is_admin = $user->abilities->where('name', 'admin:admin')->first();
        if($user->email_verified_at == null && !$is_admin){
            return response()->json(['message' => 'Please verify your email.'],401);
        }
        $abilities = $user->abilities;
        foreach ($abilities as $ability){
            $ab_array[] = $ability->name;
        }
        $token = $user->createToken($request->email, $ab_array)->plainTextToken;
        return response()->json(['message' => 'You are logged in. Welcome to the API!','token' => $token],201);
    }

    public function logOut(Request $request){
        return response()->json(['message' => 'Goodbye!','destroyed_tokens' => $request->user()->tokens()->delete()],200);
    }
}
