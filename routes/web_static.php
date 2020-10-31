<?php
    /*
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
    | Static Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "static" middleware group.
    |
    | These static routes do not do anything regarding sessions nor CSRF verification, and should not create any cookies.
    |
    */

    Route::prefix('signature/{username}')->name('signatures.')->namespace('Signatures')->group(static function () {
        Route::get('general', 'GeneralSignatureController@render')->name('general');
        Route::get('general-small', 'SmallGeneralSignatureController@render')->name('general_small');
        Route::get('general-tooltip', 'TooltipSignatureController@render')->name('general_tooltip');

        Route::get('uhc-champions', 'UHCChampionsSignatureController@render')->name('uhc_champions');
        Route::get('bedwars', 'BedWarsSignatureController@render')->name('bedwars');
        Route::get('duels', 'DuelsSignatureController@render')->name('duels');
        Route::get('tnt-games', 'TNTGamesSignatureController@render')->name('tnt_games');
        Route::get('cops-and-crims', 'CopsAndCrimsSignatureController@render')->name('cops_and_crims');

        Route::get('skywars', 'SkyWarsSignatureController@render')->name('skywars');
        Route::get('skywars-simple', 'SimpleSkyWarsSignatureController@render')->name('skywars_simple');
        Route::get('skywars-gif', 'AnimatedSkyWarsSignatureController@render')->name('skywars_gif');

        Route::get('skyblock/stats/{profile_id}', 'SkyBlock\SkyBlockSignatureController@render')->name('skyblock.stats');
        Route::get('skyblock/pets/{profile_id}', 'SkyBlock\PetsSignatureController@render')->name('skyblock.pets');
        Route::get('skyblock/minions/{profile_id}', 'SkyBlock\MinionsSignatureController@render')->name('skyblock.minions');

        Route::get('guild/general', 'Guild\GuildSignatureController@render')->name('guild.general');
        Route::get('guild/banner', 'Guild\BannerSignatureController@render')->name('guild.banner');

        Route::get('other/timestamp', 'TimestampSignatureController@render')->name('other.timestamp');
    });

    Route::get('/player/{uuid}/skin/head.webp', 'Player\ImageController@getHeadAsWebP')->name('player.skin.head');
    Route::get('/player/{uuid}/skin/head.png', 'Player\ImageController@getHeadAsPNG')->name('player.skin.head.png');

    Route::get('/player/{uuid}/skin/full.webp', 'Player\ImageController@getSkinAsWebP')->name('player.skin.full');
    Route::get('/player/{uuid}/skin/full.png', 'Player\ImageController@getSkinAsPNG')->name('player.skin.full.png');

    Route::get('/guild/{id}/banner.png', 'Guild\BannerController@getBanner')->name('guild.banner');

    Route::get('sitemap.xml', 'MetaController@getSitemap')->name('meta.sitemap');
