<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('login', 'Auth\AuthController@login');
    Route::post('signup', 'Auth\AuthController@signup');
    Route::get('activate/{token}', 'Auth\AuthController@activate');

    Route::middleware([
        'auth:api'
    ])->group(function () {
        Route::get('logout', 'Auth\AuthController@logout');
        Route::get('user', 'Auth\AuthController@user');
    });
});

Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'forum'
], function () {
    Route::get('/', 'Resources\ForumController@index');
    Route::post('/', 'Resources\ForumController@store');

    Route::get(   '/{forum}', 'Resources\ForumController@show');
    Route::patch( '/{forum}', 'Resources\ForumController@update');
    Route::delete('/{forum}', 'Resources\ForumController@destroy');

    // Posts
    Route::get('/{forum}/posts',  'Resources\PostController@index');

    Route::prefix('/{forum}/post')->group(function () {
        Route::post('/', 'Resources\PostController@store');

        Route::get('/{post}', 'Resources\PostController@show');
        Route::patch('/{post}', 'Resources\PostController@update');
        Route::delete('/{post}', 'Resources\PostController@destroy');
    });
});

Route::group([
    'prefix' => 'test'
], function() {
   Route::get('comments', 'TestController@comments');
});
