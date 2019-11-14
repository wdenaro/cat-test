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



Route::get('choose', 'MainController@choose')
    ->name('choose');


Route::post('chosen', 'MainController@chosen')
    ->name('chosen');


Route::post('build', 'MainController@build')
    ->name('build');


Route::get('pdf/{id}', 'MainController@pdf')
    ->name('pdf');




Route::get('info', function() {
   dd(phpInfo());
});


Route::get('test_a/{pdf?}', 'MainController@test_a')
    ->name('test_a');
