<?php

namespace App\Http\Controllers;

use App\Post;
use App\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum');
    }

    public function all()
    {
        $all = Post::all();
        return response()->json($all, 200);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:1|max:50',
            'content' => 'required|min:1|max:255',
            'image' => 'required|mimes:jpeg,png,svg']);
        $image = $request->image;
        $path = Storage::putFile('img/posts', new File($image));
        $post = Post::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'content' => $request->content,
            'image'=> $path]);
        return response()->json($post, 201);
    }
    
    public function show($id)
    {
        $post = Post::find($id);
        if(!$post){
            return response()->json(['error' => "The post you are looking for doesn't exists."], 404);
        }
        return response()->json($post, 200);
    }   
  
    public function update(Request $request, $id)
    {
        if($request->user()->tokenCan('admin:admin')||$post->user_id == $request->user()->id){
            $post = Post::find($id);
            if(!$post){
                return response()->json(['error' => "The post you wanted to update doesn't exists."], 400);
            }
            $request->validate([
                'title' => 'min:1|max:50',
                'content' => 'min:1|max:255',
                'image' => 'mimes:jpeg,png,svg']);
            if($request->has('image')){
                Storage::delete($post->image);
                $image = Storage::putFile('img/posts', new File($request->image));
            }else{
                $image = $post->image;
            }
            $title = $request->filled('title')? $request->title : $post->title;
            $content = $request->filled('content')? $request->content : $post->content;
            $post->update([
                    'user_id' => $request->user()->id,
                    'title' => $title,
                    'content' => $content,
                    'image' => $image]);
            $post = Post::find($id);
            return response()->json($post, 201);
            }else{
                return response()->json(['message' => "Unauthorized",], 401);
            }
    }

    public function destroyAll(Request $request)
    {
        Comment::truncate();
        Storage::deleteDirectory('img/posts');
        Storage::makeDirectory('img/posts');
        Schema::disableForeignKeyConstraints();
        Post::truncate();
        Schema::enableForeignKeyConstraints();
        return response()->json(['message' => "All the posts and comments have been deleted succesfully."], 200);
    }

    public function destroy(Request $request, $id)
    {
        if($request->user()->tokenCan('admin:admin')|| $post->user_id == $request->user()->id){
            $post = Post::find($id);
            if(!$post){
                return response()->json(['message' => "The post you wanted to delete doesn't exists."], 400);
            }
            $title = $post->title;
            Comment::where('post_id', $id)->delete();
            Storage::delete($post->image);
            Post::destroy($id);
            return response()->json(['message' => "The post titled '".$title."' has been deleted succesfully.", 'code' => 200], 200);
        }else{
            return response()->json(['message' => "Unauthorized"], 401);
        }
    }
}
