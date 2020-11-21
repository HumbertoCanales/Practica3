<?php

namespace App\Http\Controllers\ApiFunctions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Ability;

class AbilityController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum');
    }

    public function abi(Request $request)
    {
        if($request->user()->tokenCan('admin:admin')){
            $abilities = Ability::all();
            return response()->json($abilities, 200);
        }else{
            return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
        }
    }

    public function showAbi(Request $request, int $user)
    {
        if($request->user()->tokenCan('admin:admin')){
            $user_sel = User::find($user);
            if($user_sel){
                $abilities = $user_sel->abilities;
                return response()->json($abilities, 200);
            }else{
                return response()->json(['message' => "The user you are looking for doesn't exists.",
                                      'code' => 404], 404);
            }
        }else{
            return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
        }
    }

    public function grantAbi(Request $request, int $user){
        if($request->user()->tokenCan('admin:admin')){
            $user_sel = User::find($user);
        if($user_sel){
            $request -> validate([
                'ability_name' => 'required'
            ]);
            $ability = $request->ability_name;
            if(!Ability::where('name', $ability)->first()){
                return response()->json(['message' => "This ability doesn't exists.",
                                        'code' => 200],200);
            }
            if($user_sel){
                $rep_ability = $user_sel->abilities->where('name', $ability)->first();
                if(!$rep_ability){
                    $user_sel->abilities()->attach(Ability::where('name', $ability)->first());
                    return response()->json(['message' => $ability." ability granted to the user ".$user,
                                        'code' => 200],200);
                }
                return response()->json(['message' => "This ability has already been granted to this user.",
                                        'code' => 200],200);
            }
        }else{
            return response()->json(['message' => "The user you are looking for doesn't exists.",
                                      'code' => 404], 404);
        }
        }else{
            return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
        }
    }

    public function revokeAbi(Request $request, $user){
        if($request->user()->tokenCan('admin:admin')){
            $selected_user = User::find($user);
        if($selected_user){
            $request -> validate([
                'ability_name' => 'required'
            ]);
            $ability = $request->ability_name;
            if(!Ability::where('name', $ability)->first()){
                return response()->json(['message' => "This ability doesn't exists.",
                                        'code' => 200],200);
            }
            if($selected_user){
                $ability_exs = $selected_user->abilities->where('name', $ability)->first();
                if($ability_exs){
                    $selected_user->abilities()->detach(Ability::where('name', $ability)->first());
                    return response()->json(['message' => $ability." ability revoked for the user ".$user,
                                        'code' => 200],200);
                }
                return response()->json(['message' => "This ability hasn't been granted to this user.",
                                        'code' => 200],200);
            }
        }else{
            return response()->json(['message' => "The user you are looking for doesn't exists.",
                                      'code' => 404], 404);
        }
        }else{
            return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
        }
    }
}

