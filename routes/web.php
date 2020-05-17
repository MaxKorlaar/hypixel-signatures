<?php
/**
 * Copyright (c) 2020 Max Korlaar
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions, a visible attribution to the original author(s)
 *   of the software available to the public, and the following disclaimer
 *   in the documentation and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

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

    Route::get('/signatures', 'IndexController@signatureIndex')->name('signatures');

    Route::prefix('signature/{username}')->name('signatures.')->namespace('Signatures')->group(static function () {
        Route::get('general', 'GeneralSignatureController@render')->name('general');
        Route::get('general-small', 'SmallGeneralSignatureController@render')->name('general_small');
        Route::get('general-tooltip', 'TooltipSignatureController@render')->name('general_tooltip');
        Route::get('bedwars', 'BedWarsSignatureController@render')->name('bedwars');
        Route::get('skywars', 'SkyWarsSignatureController@render')->name('skywars');
        Route::get('skyblock/{profile_id?}', 'SkyBlockSignatureController@render')->name('skyblock');
    });

    Route::get('/player/{username}/uuid', 'PlayerController@getUuid')->name('player.get_uuid');
    Route::get('/player/{uuid}/profile', 'PlayerController@getProfile')->name('player.get_profile');

    Route::get('friends/{username}', 'Friends\FriendsController@getFriends')->where(['username' => '\w{32}']);

    Route::prefix('status-sig')->group(static function () {
        Route::get('get-{name}/{uuid}{other?}', 'RedirectOldSignaturesController@redirect');
    });
