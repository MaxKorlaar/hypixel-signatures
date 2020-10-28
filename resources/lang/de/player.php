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

    return [
        'title' => 'Hypixel online Spieler-Status',

        'social' => [
            'title'       => 'Hypixel online Spieler-Status - :site',
            'description' => "Überprüfen Sie den Online-Status Ihrer Freunde auf Hypixel! Sehen Sie, ob sich gerade jemand in einer Lobby befindet, ein Spiel spielt oder etwas anderes tut! Finden Sie dies und mehr auf :site"
        ],

        'callout' => [
            "Zeigen Sie an, ob ein Spieler gerade online ist, welches Spiel er spielt oder in welcher Lobby er sich befindet.",
            "Es ist ganz einfach - Sie müssen nur einen Benutzernamen eingeben, um loszulegen."
        ],

        'callout_placeholder' => 'Geben Sie einen Benutzernamen ein, um loszulegen',
        'callout_button'      => 'Status anzeigen',

        'recently_viewed'                         => 'Zuletzt angesehen',
        'recently_viewed_game_and_views'          => ':game – :views Aufrufe',
        'recently_viewed_game_mode_and_views'     => ':game (:mode) – :views Aufrufe',
        'recently_viewed_lobby_and_views'         => 'In lobby – :views Aufrufe',
        'recently_viewed_lobby_of_game_and_views' => ':game Lobby – :views Aufrufe',
        'recently_viewed_online_and_views'        => 'Online – :views Aufrufe',
        'recently_viewed_offline_and_views'       => 'Offline – :views Aufrufe',

        'status' => [
            'title' => "Online-Status von :username auf Hypixel",

            'social' => [
                'title'       => "Online-Status von :username auf Hypixel - :site",
                'description' => "Sieh dir den aktuellen Online-Status von :username auf Hypixel an. Überprüfen Sie, ob sich :username derzeit in einer Lobby befindet, ein Spiel spielt oder etwas anderes tut! Finden Sie dies und mehr auf: site"
            ],

            'error'               => 'Fehler',
            'api_status_disabled' => ':username hat sich entschieden, den aktuellen Hypixel-Online-Status vor der öffentlichen API auszublenden.',

            'name_is_currently' => ':username ist zurzeit',

            'in_lobby'         => 'In einer Lobby',
            'in_lobby_of_game' => 'In der {{ status.game }} lobby',

            'online'         => 'Online auf Hypixel',
            'online_in_game' => 'spielt {{ status.game }}',

            'mode' => 'Modalität: {{ status.mode_fancy }}',

            'offline'   => 'Offline',
            'last_seen' => 'Zuletzt gesehen {{ last_seen }}',

            'help_text' => 'Auf dieser Seite sehen Sie den aktuellen Status von :username auf dem Hypixel Minecraft-Server. Obwohl wir versuchen, den neuesten Status anzuzeigen, implementieren wir Caching, um die Leistung unserer Website zu optimieren. Daher ist es möglich, dass der aktuell angezeigte Status um etwa eine Minute veraltet ist. Wenn jemand seinen Sitzungsstatus in der Hypixel-API deaktiviert, können wir seinen tatsächlichen Status nicht nachschlagen. Diese Seite wird automatisch alle 10 Sekunden aktualisiert.'
        ],
    ];
