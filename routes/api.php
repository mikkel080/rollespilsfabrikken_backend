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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('login', 'Auth\AuthController@login');
    Route::post('signup', 'Auth\AuthController@signup');
    Route::get('activate/{token}', 'Auth\AuthController@activate');

    Route::middleware([
        'auth:sanctum'
    ])->group(function () {
        Route::get('logout', 'Auth\AuthController@logout');
        Route::get('user', 'Auth\AuthController@user');

        // TODO: REMOVE COMMENTS
        // Get permissions
        Route::get('/permission', 'Auth\PermissionController@index'); // DONE - tested
        Route::get('/permission/{permission}', 'Auth\PermissionController@show'); // DONE - tested
        Route::get('/calendar/{calendar}/permission', 'Auth\PermissionController@calendarIndex'); // DONE - tested
        Route::get('/forum/{forum}/permission', 'Auth\PermissionController@forumIndex'); // DONE - tested

        // Get permissions from role in the context of an obj
        Route::get('/role/{role}/forum/{forum}/permission', 'Auth\PermissionRoleController@forumIndex'); // DONE - tested
        Route::get('/role/{role}/calendar/{calendar}/permission', 'Auth\PermissionRoleController@calendarIndex'); // DONE - tested

        // Create edit, delete roles
        Route::resource('role', 'Auth\RoleController'); // DONE - tested

        // Add permissions to roles in different ways.
        Route::post('/permission/{permission}/role/{role}', 'Auth\PermissionRoleController@permissionAdd'); // DONE - tested
        Route::post('/role/{role}/permission/{permission}', 'Auth\PermissionRoleController@roleAdd'); // DONE - tested
        Route::post('/calendar/{calendar}/level/{level}/role/{role}', 'Auth\PermissionRoleController@calendarAdd'); // DONE - tested
        Route::post('/forum/{forum}/level/{level}/role/{role}', 'Auth\PermissionRoleController@forumAdd'); // DONE - tested

        Route::delete('/permission/{permission}/role/{role}', 'Auth\PermissionRoleController@permissionDelete'); // DONE - tested
        Route::delete('/role/{role}/permission/{permission}', 'Auth\PermissionRoleController@roleDelete'); // DONE - tested
        Route::delete('/calendar/{calendar}/level/{level}/role/{role}', 'Auth\PermissionRoleController@calendarDelete'); // DONE - tested
        Route::delete('/forum/{forum}/level/{level}/role/{role}', 'Auth\PermissionRoleController@forumDelete'); // DONE - tested

        // Assign, index and delete roles from user
        Route::get('/user/{user}/role', 'Auth\UserRoleController@index');
        Route::post('/user/{user}/role/{role}', 'Auth\UserRoleController@add');
        Route::delete('/user/{user}/role/{role}', 'Auth\UserRoleController@delete');
    });
});

Route::group([
    'middleware' => 'auth:sanctum',
], function () {
    Route::apiResource('forum', 'Resources\ForumController');
    Route::apiResource('forum.post', 'Resources\PostController');
    Route::apiResource('forum.post.comment', 'Resources\CommentController');
});

Route::group([
    'middleware' => 'auth:sanctum',
], function () {
    Route::apiResource('calendar', 'Resources\CalendarController');
    Route::apiResource('calendar.event', 'Resources\EventController');
});
