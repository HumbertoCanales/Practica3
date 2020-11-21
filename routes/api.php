<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiFunctions;

//Auth
Route::post('/signup','ApiAuth\AuthController@signUp');
Route::post('/login','ApiAuth\AuthController@logIn');
Route::delete('/logout','ApiAuth\AuthController@logOut')->middleware('auth:sanctum');
Route::post('/email/resend','ApiAuth\VerificationController@resend')->name('verificationapi.resend');
Route::get('/email/verify/{id}/{hash}','ApiAuth\VerificationController@verify')->name('verificationapi.verify');

//Admins
//   can -> admin:admin
Route::middleware('tokenCan:admin:admin')->group(function(){
    Route::get('users/abilities','ApiFunctions\AbilityController@abi');
    Route::get('users/{user}/abilities','ApiFunctions\AbilityController@showAbi');
    Route::post('users/{user}/abilities','ApiFunctions\AbilityController@grantAbi');
    Route::delete('users/{user}/abilities','ApiFunctions\AbilityController@revokeAbi');
    
    Route::put('/users/{user}','UserController@update');
    Route::delete('/users/{user}','UserController@destroy');
    
    Route::get('/posts/comments','CommentController@allPC');
    Route::get('/comments','CommentController@all');
    Route::get('/comments/{comment}','CommentController@show');
    
    Route::delete('/posts','PostController@destroyAll');
    Route::delete('/comments','CommentController@destroyAll');
    Route::delete('/posts/{post}/comments','CommentController@destroyFromPost');
});

//Logged-in Users
//   can -> nyt:data
Route::middleware('tokenCan:nyt:data')->group(function(){
    Route::get('/nyt/search','ApiFunctions\NYTimesController@search');
    Route::get('/nyt/popular/{period}','ApiFunctions\NYTimesController@mostPopular');
    Route::get('/nyt/books','ApiFunctions\NYTimesController@books');
});

//   can -> user:info
Route::middleware('tokenCan:user:info')->group(function(){
    Route::get('/users','UserController@all');
    Route::get('/users/{user}','UserController@show');
});

//   can -> user:profile
Route::get('/profile','UserController@showMP')->middleware('tokenCan:user:profile');
Route::post('/profile','UserController@updateMP')->middleware('tokenCan:user:profile');

//   can -> post:publish
Route::post('/posts','PostController@store')->middleware('tokenCan:post:publish');
//   can -> post:edit
Route::post('/posts/{post}','PostController@update')->middleware('tokenCan:post:edit');
//   can -> post:delete
Route::delete('/posts/{post}','PostController@destroy')->middleware('tokenCan:post:delete');

//   can -> com:publish
Route::post('/posts/{post}/comments','CommentController@store')->middleware('tokenCan:com:publish');
//   can -> com:edit
Route::put('/posts/{post}/comments/{comment}','CommentController@update')->middleware('tokenCan:com:edit');
//   can -> com:delete
Route::delete('/posts/{post}/comments/{comment}','CommentController@destroy')->middleware('tokenCan:com:delete');

//All Users
Route::get('/posts','PostController@all');
Route::get('/posts/{post}','PostController@show');

Route::get('/posts/{post}/comments','CommentController@allFromPost');
Route::get('/posts/{post}/comments/{comment}','CommentController@showFromPost');