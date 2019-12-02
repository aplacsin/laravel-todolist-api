<?php

use Illuminate\Http\Request;
use Http\Controllers\TaskController;

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

/* Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */


Route::group(['middleware' => 'api'], function () {
    Route::get('/tasks', 'TaskController@index');
    Route::get('/{id}/completed', 'TaskController@completed');
    Route::post('/create', 'TaskController@store');
    Route::post('/{id}/update', 'TaskController@update');
    Route::delete('/{id}/delete', 'TaskController@destroy');
    Route::post('/subtask/{parent_id}/create', 'TaskController@createsubtask');
    Route::get('/tasks/filter', 'TaskController@filter');
});


