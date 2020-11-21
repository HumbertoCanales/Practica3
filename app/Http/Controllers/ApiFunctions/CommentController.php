<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Post;
use App\User;
use Illuminate\Http\Request;
use App\Mail\CommentPublished;
use App\Mail\CommentReceived;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum');
    }

    public function all(Request $request)
    {
        if($request->user()->tokenCan('admin:admin')){
            $comments = Comment::all();
            return response()->json($comments, 200);
        }else{
            return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
        }
    }

    public function allPC(Request $request)
    {
        if($request->user()->tokenCan('admin:admin')){
            $posts = Post::all();
            foreach ($posts as $post) {
                $post['comments'] = $post->comments;
                $PC[] = $post;
            }
            return response()->json($PC, 200);
        }else{
            return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
        }
    }

    public function allFromPost(int $post)
    {
        $post_sel = Post::find($post);
        if($post_sel){
            $comments = $post_sel->comments;
        }else{
                return response()->json(['message' => "The post you are looking for doesn't exists.",
                                          'code' => 404], 404);
            }
            if(count($comments)){
                return response()->json($comments, 200);
            }else{
                return response()->json(['message' => "This post doesn't have any comments.",
                                          'code' => 200], 200);
            }
    }

    public function show(Request $request, int $comment)
    {
            $comment_sel = Comment::find($comment);
            if($comment_sel){
                return response()->json($comment_sel, 200);
            }else{
                return response()->json(['message' => "The comment you are looking for doesn't exists.",
                                        'code' => 404], 404);
            }
    }
    
    public function showFromPost(Request $request, int $post, int $comment)
    {
            $post_sel = Post::find($post);
            if(!$post_sel){
                return response()->json(['message' => "The post you are looking for doesn't exists.",
                                        'code' => 404], 404);
            }
            $comment_sel = Comment::find($comment);
            if(!$comment_sel){
                return response()->json(['message' => "The comment you are looking for doesn't exists.",
                                        'code' => 404], 404);
            }
            if($comment_sel['post_id']!=$post_sel->id){
                return response()->json(['message' => "This comment doesn't belong to this post.",
                                        'code' => 422], 422);
            }
            return response()->json($comment_sel, 200);
            
    } 
    
    public function store(Request $request, int $post)
    {
        $sel_post = Post::find($post);
        if($sel_post){
            if($request->user()->tokenCan('admin:admin') || $request->user()->tokenCan('com:publish')){
                $request->validate([
                    'content' => 'required|min:1|max:255'
                ]);
                $comment = Comment::create(
                        ['post_id' => $sel_post->id,
                         'content' => $request->content,
                         'user_id' => $request->user()->id]);
                $post_au = User::find($sel_post->user_id);
               //dd($post_au->name);
                Mail::to($request->user())->send(new CommentPublished($sel_post->title, $comment->content));
                if($post_au->id != $comment->user_id){
                    Mail::to($post_au)->send(new CommentReceived($request->user()->name, $sel_post->title, $comment->content));
                }
                return response()->json($comment, 201);
            }else{
                return response()->json(['message' => "Unauthorized",
                                          'code' => 401], 401);
            }
        }else{
            return response()->json(['message' => "The post you want to comment on doesn't exists.",
                                     'code' => 400], 400);
        }
    }

    public function update(Request $request, Post $post, $id)
    {
        $comment = Comment::find($id);
        if($comment){
            if($request->user()->tokenCan('admin:admin')
            || $request->user()->tokenCan('com:edit') && $comment->user_id == $request->user()->id){
                $validator = Validator::make($request->all(), [
                    'content' => 'required|min:1|max:255'
                ]);
                $errors = $validator->errors();
                
                if($validator->fails()){
                    return response()->json($errors, 400);
                }else{
                    Comment::find($id) -> update(
                        ['content' => $request['content']]);
                    $comment = Comment::find($id);
                    return response()->json($comment, 201);
                }
            }else{
                return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
            }
        }else{
                return response()->json(['message' => "The comment you wanted to update doesn't exists.",
                                         'code' => 400], 400);
        }
    }

    public function destroyAll(Request $request)
    {
        if($request->user()->tokenCan('admin:admin')){
            Comment::truncate();
            return response()->json(['message' => "All the comments have been deleted succesfully.", 'code' => 200], 200);
        }else{
            return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
        }
    }

    public function destroyFromPost(Request $request, int $post)
    {
        if($request->user()->tokenCan('admin:admin')){
            $post_sel = Post::find($post);
            if($post_sel){
                $comments = $post_sel->comments->destroy();
                return response()->json(['message' => "All the comments from the post '".$post['id']."' have been deleted succesfully.", 
                                            'code' => 200], 200);
            }else{
                return response()->json(['error' => "The post you are looking for doesn't exists.",
                                          'code' => 400], 400);
            }
        }else{
            return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
        }
    }

    public function destroy(Request $request, Post $post, $id)
    {
        $comment = Comment::find($id);
        if($comment){
            if($request->user()->tokenCan('admin:admin')
            || $request->user()->tokenCan('com:edit') && $comment->user_id == $request->user()->id){
                Comment::destroy($id);
                return response()->json(['message' => "The comment with the id '".$id."' has been deleted succesfully.",
                                     'code' => 200], 200);
            }else{
                return response()->json(['message' => "Unauthorized",
                                      'code' => 401], 401);
            }
        }else{
            return response()->json(['message' => "The comment you wanted to delete doesn't exists.",
                                     'code' => 400], 400);
        }
    }
}
