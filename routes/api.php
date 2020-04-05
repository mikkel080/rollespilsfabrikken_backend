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
    Route::post('login',            'Auth\AuthController@login');
    Route::post('signup',           'Auth\AuthController@signup');
    Route::get('activate/{token}',  'Auth\AuthController@activate');
    Route::post('/resend-email',    'Auth\AuthController@resendEmail');

    Route::middleware([
        'auth:sanctum'
    ])->group(function () {

        Route::get('logout', 'Auth\AuthController@logout');

        // Get permissions related to object
        Route::get('/calendar/{calendar}/permission',   'Auth\PermissionController@calendarIndex');
        Route::get('/forum/{forum}/permission',         'Auth\PermissionController@forumIndex');

        // Add level permission from object to role
        Route::post('/calendar/{calendar}/level/{level}/role/{role}',   'Auth\PermissionRoleController@calendarAdd');
        Route::post('/forum/{forum}/level/{level}/role/{role}',         'Auth\PermissionRoleController@forumAdd');

        // Delete objects level permission from role
        Route::delete('/calendar/{calendar}/level/{level}/role/{role}', 'Auth\PermissionRoleController@calendarDelete');
        Route::delete('/forum/{forum}/level/{level}/role/{role}',       'Auth\PermissionRoleController@forumDelete');

        Route::prefix('permission')->group(function () {
            Route::get('/',                             'Auth\PermissionController@index');
            Route::get('/{permission}',                 'Auth\PermissionController@show');
            Route::delete('/{permission}/role/{role}',  'Auth\PermissionRoleController@permissionDelete');
            Route::post('/{permission}/role/{role}',    'Auth\PermissionRoleController@permissionAdd');
        });

        Route::prefix('role')->group(function () {// Get permissions from role in the context of an obj
            // Create edit, delete roles
            Route::resource('/', 'Auth\RoleController');

            // Index permissions in roles
            Route::get('/{role}/forum/{forum}/permission',          'Auth\PermissionRoleController@forumIndex');
            Route::get('/{role}/calendar/{calendar}/permission',    'Auth\PermissionRoleController@calendarIndex');

            // Add permissions to roles in different ways.
            Route::post('/{role}/permission/{permission}',      'Auth\PermissionRoleController@roleAdd');
            Route::delete('/{role}/permission/{permission}',    'Auth\PermissionRoleController@roleDelete');
        });

        Route::prefix('user')->group(function () {
            Route::get('/', 'Auth\AuthController@user');

            // Assign, index and delete roles from user
            Route::get('/{user}/role',              'Auth\UserRoleController@index');
            Route::post('/{user}/role/{role}',      'Auth\UserRoleController@add');
            Route::delete('/{user}/role/{role}',    'Auth\UserRoleController@delete');
        });
    });
});


Route::group([
    'middleware' => 'auth:sanctum',
], function () {
    Route::apiResource('forum',                 'Resources\ForumController');
    Route::apiResource('forum.post',            'Resources\PostController');
    Route::apiResource('forum.post.comment',    'Resources\CommentController');
});

Route::group([
    'middleware' => 'auth:sanctum',
], function () {
    Route::apiResource('calendar',          'Resources\CalendarController');
    Route::apiResource('calendar.event',    'Resources\EventController');
});
