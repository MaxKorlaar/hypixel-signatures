<?php
    /*
 * Copyright (c) 2020-2025 Max Korlaar
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

    return [
        'title' => 'Hypixel Speler Online Status',

        'social' => [
            'title'       => 'Hypixel Speler Online Status - :site',
            'description' => 'Bekijk de online-status van je vrienden op Hypixel! Je kan zien of iemand in een lobby zit, aan het spelen is of iets anders doet! Bekijk dit en meer op :site',
        ],

        'callout' => [
            'Bekijk of een speler momenteel online is, welk spel ze spelen of in welke lobby ze zich bevinden.',
            'Het is gemakkelijk – Je hoeft alleen een gebruikersnaam in te voeren om te beginnen.'
        ],

        'callout_placeholder' => 'Voer een gebruikersnaam in om te beginnen',
        'callout_button'      => 'Bekijk status',

        'recently_viewed'                         => 'Recent bekeken',
        'recently_viewed_game_and_views'          => ':game – :views keer bekeken',
        'recently_viewed_game_mode_and_views'     => ':game (:mode) – :views keer bekeken',
        'recently_viewed_lobby_and_views'         => 'In lobby – :views keer bekeken',
        'recently_viewed_lobby_of_game_and_views' => ':game Lobby – :views keer bekeken',
        'recently_viewed_online_and_views'        => 'Online – :views keer bekeken',
        'recently_viewed_offline_and_views'       => 'Offline – :views keer bekeken',

        'status' => [
            'title' => ":username's online status op Hypixel",

            'social' => [
                'title'       => ":username's online status op Hypixel - :site",
                'description' => "Bekijk de huidige online status van :username's op Hypixel: Zie of :username momenteel in een lobby zit, een spel aan het spelen is of iets anders doet! Bekijk dit en meer op :site",
            ],

            'tweet_text' => 'Bekijk de online status van :username op Hypixel!',

            'error'               => 'Fout',
            'api_status_disabled' => ':username heeft ervoor gekozen om zijn of haar online-status op Hypixel te verbergen in de Hypixel-API.',

            'name_is_currently' => ':username is momenteel',

            'in_lobby'         => 'In een lobby',
            'in_lobby_of_game' => 'In de {{ status.game }} lobby',

            'online'         => 'Online op Hypixel',
            'online_in_game' => '{{ status.game }} aan het spelen',

            'mode' => 'Mode: {{ status.mode_fancy }}',

            'offline'   => 'Offline',
            'last_seen' => 'Laatst gezien {{ last_seen }}',

            'help_text' => 'Op deze pagina zie je de huidige online status van :username op de Hypixel Minecraft-server. Ondanks dat we proberen om de meest recente status te tonen, kan het zijn dat de resultaten ongeveer tot een minuut oud zijn door het gebruik van caching. Als iemand ervoor kiest om de sessie-status uit te schakelen voor hun account in de Hypixel-API, is het voor ons onmogelijk om hun huidige status op te zoeken. Deze pagina ververst automatisch elke 30 seconden.',
        ],
    ];
