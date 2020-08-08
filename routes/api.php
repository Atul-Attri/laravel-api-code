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

/** V1 Routes */
Route::group(['prefix' => 'v1', 'middleware' => 'enforce-json'], function () {

    /** Public Routes */
    Route::get('/', function () {
        return response()->json([
            'meta' => [
                'code' => 200,
                'message' => 'OK'
            ], 'data' => ['app' => env('APP_VERSION', '1.0.0')]
        ], 200);
    });

    Route::group(['namespace' => 'API'], function () {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');

        /** Authenticated Routes */
        Route::group(['middleware' => 'auth:api'], function () {

            Route::delete('logout', 'AuthController@logout');

            /** Profile Routes */
            Route::group(['prefix' => 'profile'], function () {
                Route::get('get', 'UsersController@getMyProfile');
                Route::get('{id}/view', 'UsersController@getProfile');
                Route::put('update', 'UsersController@updateMyProfile');
                Route::post('list', 'UsersController@list');
            });
        });
    });
});