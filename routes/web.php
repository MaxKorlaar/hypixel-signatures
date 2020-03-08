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

    Route::get('/', 'IndexController@index')->name('home');

    Route::prefix('signature/{uuid}')->where(['uuid' => '\w{32}'])->name('signatures.')->namespace('Signatures')->group(static function () {
        Route::get('general', 'GeneralController@render')->name('general');
    });
