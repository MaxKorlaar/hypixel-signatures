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

    Route::prefix('signature/{uuid}')->name('signatures.')->namespace('Signatures')->group(static function () {
        Route::get('general', 'GeneralSignatureController@render')->name('general');
        Route::get('general-small', 'SmallGeneralSignatureController@render')->name('general_small');
        Route::get('general-tooltip', 'TooltipSignatureController@render')->name('general_tooltip');
    });

    Route::get('friends/{uuid}', 'Friends\FriendsController@getFriends')->where(['uuid' => '\w{32}']);

    Route::prefix('status-sig')->group(static function () {
        Route::get('get-{name}/{uuid}{other?}', 'RedirectOldSignaturesController@redirect');
    });
