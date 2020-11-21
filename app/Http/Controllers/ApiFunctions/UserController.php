<?php

namespace App\Http\Controllers;

use App\User;
use App\Post;
use App\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum');
    }

    public function all(Request $request)
    {
        $all = User::all();
        return response()->json($all, 200);
    }
    
    public function show(Request $request, $id)
    {
        $user = User::find($id);
        if(!$user){
            return response()->json(['error' => "The post you are looking for doesn't exists."], 404);
        }
        return response()->json($user, 200);
    }

    public function showMP(Request $request)
    {
        $user = User::find($request->user()->id);
        return response()->json($user, 200);
    }     
    
    public function updateMP(Request $request)
    {
        $request -> validate([
            'email' => 'email',
            'name' => 'min:1|max:30',
            'age' => 'max:999',
            'password' => 'min:6',
            'image' => 'mimes:jpeg,png,svg'
        ]);
        $user = User::find($request->user()->id);
                
        if($request->has('image')){
            Storage::delete($user->image);
            $image = Storage::putFile('img/profilepics', new File($request->image));
        }else{
            $image = $user->image;
        }
                
        if($request->filled('email')){
            if($request->email != $user->email){
                $user_f = User::where('email', $request->email)->first();
                if($user_f){
                    return response()->json(['email' => "This email has already been taken."], 422);
                }
            }
            $email = $request->email;
        }else{
            $email = $user->email;
        }
        $name = $request->filled('name')? $request->name : $user->name;
        $age = $request->filled('age')? $request->age : $user->age;
        $password = $request->filled('password')? $request->password : $user->password;

        $user->update([
            'email' => $email,
            'name' => $name,  
            'age' => $age,
            'password' => Hash::make($password),
            'image' => $image]);
        $user = User::find($request->user()->id);
        if($request->filled('email')){
            if($request->email != $user->email){
                $user->email_verified_at = null;
                $user->sendApiEmailVerificationNotification();
            }
        }
        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if(!$user){
            return response()->json(['error' => "The user you wanted to update doesn't exists."], 400);
        }
        $request -> validate([
            'email' => 'email',
            'name' => 'min:1|max:30',
            'age' => 'max:999',
            'password' => 'min:6',
            'image' => 'mimes:jpeg,png,svg'
        ]);
        if($request->has('image')){
            Storage::delete($user->image);
            $image = Storage::putFile('img/profilepics', new File($request->image));
        }else{
            $image = $user->image;
        }
                
        if($request->filled('email')){
            if($request->email != $user->email){
                $user_f = User::where('email', $request->email)->first();
                if($user_f){
                    return response()->json(['email' => "This email has already been taken."], 422);
                }
            }
            $email = $request->email;
        }else{
            $email = $user->email;
        }
        $name = $request->filled('name')? $request->name : $user->name;
        $age = $request->filled('age')? $request->age : $user->age;
        $password = $request->filled('password')? $request->password : $user->password;

        $user->update([
            'email' => $request->email,
            'name' => $request->name,
            'age' => $request->age,
            'password' => Hash::make($request->password)]);
        $user = User::find($id);
        if($request->filled('email')){
            if($request->email != $user->email){
                $user->email_verified_at = null;
                $user->sendApiEmailVerificationNotification();
            }
        }
        return response()->json($user, 201);
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
        if(!$user){
            return response()->json(['message' => "The user you wanted to delete doesn't exists."], 400);
        }
        $name = $user->name;
        Comment::where('user_id', $id)->delete();
        Post::where('user_id', $id)->delete();
        $user->tokens()->delete();
        Storage::delete($user->image);
        User::destroy($id);
        return response()->json(['message' => "The user '".$name."' has been deleted succesfully."], 200);
            
    }
}

