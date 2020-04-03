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
], function () {
    Route::apiResource('forum', 'Resources\ForumController');
    Route::apiResource('forum.post', 'Resources\PostController');
    Route::apiResource('forum.post.comment', 'Resources\CommentController');
});

Route::group([
    'middleware' => 'auth:api',
], function () {
    Route::apiResource('calendar', 'Resources\CalendarController');
    Route::apiResource('calendar.event', 'Resources\EventController');
});
