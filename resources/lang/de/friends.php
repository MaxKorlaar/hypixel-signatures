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

    return [
        'title' => 'Listen von Hypixel\' Freunden',

        'callout'             => [
            "Zeigen Sie eine Liste Ihrer Freunde oder einer anderen Person auf Hypixel an, Wie lange bist du schon mit ihnen befreundet und mehr",
            "It's easy – You only have to enter your username to get started."
        ],
        'callout_placeholder' => 'Geben Sie Ihren Benutzernamen und Passwort einloggen.',
        'callout_button'      => 'Freunde anzeigen',

        'recently_viewed'                   => 'Zuletzt angesehene',
        'recently_viewed_friends_and_views' => ':count Freunden – :views Aufrufe',

        'list' => [
            'page_title' => "Hypixel-Freundesliste des :username",
            'title'      => "Freunde des :username auf Hypixel",

            'social' => [
                'title'       => ":username Freunden auf Hypixel - :site",
                'description' => 'Alle anzeigen von :total_friends Freunden of :username auf dem Hypixel Minecraft Server. Sie können die Liste durchsuchen und zu anderen Hypixel-Playern navigieren.',
            ],

            'description' => [
                "Auf dieser Seite finden Sie eine Übersicht aller :usersname :total_friends Freunden auf Hypixel. Sie können ihre Ränge sehen und darauf klicken, um Freundeslisten anderer Spieler anzuzeigen.",
                "Wenn Sie <a href=':form_link'>dynamische Signaturbilder für Folgendes generieren möchten :username</a>, das kannst du auch machen auf :site! Angeberei :username Minispiel oder SkyBlock-Statistiken und Erfolge auf jeder Website, auf der Sie durch <a href=':form_link'>Erstellen einer dynamischen Signatur</a> auf Bilder verlinken können."
            ],

            'tweet_text' => "Ich stöbere Freundesliste des :username auf Hypixel!",

            'loading_friends' => 'Freunde laden ({{ meta.loaded }} / {{ meta.total_friends }})…',
            'friends_since'   => 'schon seit {{ new Date(player.since).toLocaleDateString() }}'
        ]
    ];
