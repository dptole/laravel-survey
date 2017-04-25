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

  Route::group(['prefix' => 'r'], function() {
    Route::get('/js.js', ['uses' => 'ResourceController@js', 'as' => 'js']);
    Route::get('/css.css', ['uses' => 'ResourceController@css', 'as' => 'css']);
  });

  Route::group(['middleware' => 'auth', 'prefix' => 'dashboard'], function() {
    Route::get('/', ['uses' => 'DashboardController@getDashboard', 'as' => 'dashboard']);

    Route::group(['prefix' => 'survey'], function() {
      Route::get('/create', ['uses' => 'SurveyController@create', 'as' => 'survey.create']);
      Route::post('/create', ['uses' => 'SurveyController@store', 'as' => 'survey.store']);

      Route::get('/{uuid}/delete', ['uses' => 'SurveyController@destroy', 'as' => 'survey.destroy']);

      Route::get('/{uuid}/edit', ['uses' => 'SurveyController@edit', 'as' => 'survey.edit']);
      Route::post('/{uuid}/edit', ['uses' => 'SurveyController@update', 'as' => 'survey.update']);

      Route::get('/{uuid}/question/create', ['uses' => 'QuestionController@create', 'as' => 'question.create']);
      Route::post('/{uuid}/question/create', ['uses' => 'QuestionController@store', 'as' => 'question.store']);
    });
  });
});

