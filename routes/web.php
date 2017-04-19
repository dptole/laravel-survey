<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'laravel'], function() {
  Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
  Auth::routes();

  Route::group(['middleware' => 'auth'], function() {
    Route::get('/dashboard', ['uses' => 'DashboardController@getDashboard', 'as' => 'dashboard']);
    Route::get('/dashboard/survey/create', ['uses' => 'SurveyController@create', 'as' => 'survey.create']);
    Route::post('/dashboard/survey/create', ['uses' => 'SurveyController@store', 'as' => 'survey.store']);
    Route::get('/dashboard/survey/{uuid}/delete', ['uses' => 'SurveyController@destroy', 'as' => 'survey.destroy']);
    Route::get('/dashboard/survey/{uuid}/edit', ['uses' => 'SurveyController@edit', 'as' => 'survey.edit']);
    Route::post('/dashboard/survey/{uuid}/edit', ['uses' => 'SurveyController@update', 'as' => 'survey.update']);
  });
});

