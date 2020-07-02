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
    Route::get('/security-question','Auth\SecurityQuestionController@show');

    // Reset passwords
    Route::group([
        'namespace' => 'Auth',
        'prefix' => 'password',
    ], function () {
        Route::post('forgot',       'PasswordResetController@create');
        Route::get('find/{token}',  'PasswordResetController@find');
        Route::post('reset',        'PasswordResetController@reset');
    });

    Route::group([
        'namespace' => 'Auth',
        'middleware' => 'auth:sanctum'
    ], function () {

        Route::get('logout', 'AuthController@logout');

        // Get permissions related to object
        Route::get('/calendar/{calendar}/permission',   'PermissionController@calendarIndex');
        Route::get('/forum/{forum}/permission',         'PermissionController@forumIndex');

        // Add level permission from object to role
        Route::post('/calendar/{calendar}/level/{level}/role/{role}',   'PermissionRoleController@calendarAdd');
        Route::post('/forum/{forum}/level/{level}/role/{role}',         'PermissionRoleController@forumAdd');

        // Delete objects level permission from role
        Route::delete('/calendar/{calendar}/level/{level}/role/{role}', 'PermissionRoleController@calendarDelete');
        Route::delete('/forum/{forum}/level/{level}/role/{role}',       'PermissionRoleController@forumDelete');

        // Permissions
        Route::prefix('permission')->group(function () {
            Route::get('/',                             'PermissionController@index');
            Route::get('/{permission}',                 'PermissionController@show');
            Route::post('/{permission}/role/{role}',    'PermissionRoleController@permissionAdd');
            Route::delete('/{permission}/role/{role}',  'PermissionRoleController@permissionDelete');
        });

        // Add multiple permissions at the same time
        Route::post('/role/{role}/permissions', 'PermissionRoleController@multiAdd');
        Route::delete('/role/{role}/permissions', 'PermissionRoleController@multiDelete');

        // Create edit, delete roles
        Route::resource('role', 'RoleController');
        Route::prefix('role')->group(function () {// Get permissions from role in the context of an obj

            // Index permissions in roles
            Route::get('/{role}/forum/{forum}/permission',          'PermissionRoleController@forumIndex');
            Route::get('/{role}/calendar/{calendar}/permission',    'PermissionRoleController@calendarIndex');

            // Add permissions to roles in different ways.
            Route::post('/{role}/permission/{permission}',      'PermissionRoleController@roleAdd');
            Route::delete('/{role}/permission/{permission}',    'PermissionRoleController@roleDelete');
        });

        Route::prefix('user')->group(function () {
            // Assign, index and delete roles from user
            Route::get('/{user}/role',              'UserRoleController@index');
            Route::post('/{user}/role/{role}',      'UserRoleController@add');
            Route::delete('/{user}/role/{role}',    'UserRoleController@delete');
        });
    });
});

Route::group([
    'namespace' => 'Auth',
    'middleware' => 'auth:sanctum',
    'prefix' => 'user',
], function () {
    Route::get('/',             'UserController@user');

    // Index users
    Route::get('/index',             'UserController@index');

    // Update own username
    Route::patch('/username',   'UserController@updateUsername');

    // Permanently delete user
    Route::delete('/',          'UserController@destroySelf');
    Route::delete('/{user}',    'UserController@destroy');

    // Restrict access to forum without deleting user
    Route::post('/{user}/ban',  'UserController@ban');
    Route::post('/{user}/unban','UserController@unban');

    // Op to superuser
    Route::post('/{user}/op',  'UserController@op');
    Route::post('/{user}/deop','UserController@deop');

    // Reset user
    Route::post('/{user}/reset',    'UserController@reset');
    Route::delete('/{user}/clear',  'UserController@clear');

    // Avatar update
    Route::post('avatar', 'UserController@avatar');

    // Tokens
    Route::get('/token',                    'UserController@indexTokens');
    Route::delete('/token/{token}/revoke',  'UserController@revokeToken');
});

Route::apiResource('securityQuestion', 'Auth\SecurityQuestionController')->except([
    'show'
])->middleware('auth:sanctum');

Route::group([
    'namespace' => 'Resources',
    'middleware' => 'auth:sanctum',
], function () {
    // Register resources
    Route::apiResource('forum',                 'ForumController');
    Route::apiResource('forum.post',            'PostController');
    Route::apiResource('forum.post.comment',    'CommentController');
    Route::apiResource('calendar',              'CalendarController');
    Route::apiResource('calendar.event',        'EventController');
    Route::get('/events',                       'EventController@all');

    // Pin posts
    Route::post('/forum/{forum}/post/{post}/pin',                   'PostController@pin');

    // Lock posts
    Route::post('/forum/{forum}/post/{post}/lock',                   'PostController@lock');

    // Pin comments
    Route::post('/forum/{forum}/post/{post}/comment/{comment}/pin', 'CommentController@pin');

    // Get posts file
    Route::post('/forum/{forum}/post/{post}/file',                  'PostController@file');
    Route::get('/forum/{forum}/post/{post}/file/{file}',            'PostController@getFile');
    Route::get('/post',                                             'PostController@newest');

    Route::put('/forums/priorities', 'ForumPriorityController@priorities');
    Route::put('/forum/{forum}/priority', 'ForumPriorityController@priority');
});
