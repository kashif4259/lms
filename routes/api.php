<?php

use Illuminate\Http\Request;

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


Route::group(['namespace' => 'API', 'middleware' => 'auth:api'], function(){
    Route::group(['middleware' => 'can:admin'], function(){
        
        Route::group(['prefix' => 'users'], function() {
            Route::get('/', 'UserController@index');
            Route::post('create', 'UserController@create');
            Route::delete('delete/{id}', 'UserController@destroy');
            Route::put('{id}', 'UserController@update');
            Route::get('search', 'UserController@search');
        });

        Route::group(['prefix' => 'leads'], function(){
            Route::post('yelp/find', 'LeadsController@findLeadsFromYelp');
            Route::get('/', 'LeadsController@getLeads');
        });

    });

    Route::get('users/profile', 'UserController@profile');
    Route::put('users/profile/{id}', 'UserController@UpdateProfile');
});

// Route::group(['prefix' => 'departments', 'namespace' => 'API', 'middleware' => ['auth:api','can:admin']], function() {
//     Route::get('/', 'DepartmentController@index');
//     Route::post('create', 'DepartmentController@create');
//     Route::put('{id}', 'DepartmentController@update');
//     Route::delete('delete/{id}', 'DepartmentController@destroy');
// });



// Route::apiResources([
//     'user' => 'API\UserController',
// ]);
