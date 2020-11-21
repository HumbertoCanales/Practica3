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
        $abilities = Ability::all();
        return response()->json($abilities, 200);
    }

    public function showAbi(Request $request, int $user)
    {
        $user_sel = User::find($user);
        if(!$user_sel){
            return response()->json(['message' => "The user you are looking for doesn't exists.",
                                      'code' => 404], 404);
        }
        $abilities = $user_sel->abilities;
        return response()->json($abilities, 200);
    }

    public function grantAbi(Request $request, int $user){
        $user_sel = User::find($user);
        if(!$user_sel){
            return response()->json(['message' => "The user you are looking for doesn't exists."], 404);
        }
        $request -> validate([
            'ability_name' => 'required'
        ]);
        $ability = $request->ability_name;
        if(!Ability::where('name', $ability)->first()){
            return response()->json(['message' => "This ability doesn't exists."],200);
        }
        $rep_ability = $user_sel->abilities->where('name', $ability)->first();
        if($rep_ability){
            return response()->json(['message' => "This ability has already been granted to this user."], 200);
        }
        $user_sel->abilities()->attach(Ability::where('name', $ability)->first());
        return response()->json(['message' => $ability." ability granted to the user ".$user],200);
    }

    public function revokeAbi(Request $request, $user){
        $selected_user = User::find($user);
        if(!$selected_user){
            return response()->json(['message' => "The user you are looking for doesn't exists."],404);
        }
        $request -> validate([
            'ability_name' => 'required'
        ]);
        $ability = $request->ability_name;
        if(!Ability::where('name', $ability)->first()){
            return response()->json(['message' => "This ability doesn't exists."], 200);
        }
        $ability_exs = $selected_user->abilities->where('name', $ability)->first();
        if(!$ability_exs){
            return response()->json(['message' => "This ability hasn't been granted to this user."], 200);
        }
        $selected_user->abilities()->detach(Ability::where('name', $ability)->first());
        return response()->json(['message' => $ability." ability revoked for the user ".$user],200);
    }
}

