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
        'title' => 'Hypixel Freundesliste',

        'callout'             => [
            'Zeigt eine Liste Ihrer Freunde oder einer anderen Person auf Hypixel an, wie lange du schon mit ihnen befreundet bist und weiteres',
            'Ganz einfach – Gib einfach einen Benutzernamen ein um loszulegen.'
        ],
        'callout_placeholder' => 'Geben Sie Ihren Benutzernamen um zu starten',
        'callout_button'      => 'Freunde anzeigen',

        'recently_viewed'                   => 'Zuletzt angesehen',
        'recently_viewed_friends_and_views' => ':count Freunde – :views Aufrufe',

        'list' => [
            'page_title' => 'Hypixel-Freundesliste von :username',
            'title'      => 'Freunde von :username auf Hypixel',

            'social' => [
                'title'       => 'Freunde von :username auf Hypixel - :site',
                'description' => 'Sehen Sie sich alle Freunde von :total_friends Freunde von :username auf dem Hypixel Minecraft-Server an. Sie können durch die Liste blättern und zu anderen Hypixel-Spielern navigieren.',
            ],

            'description' => [
                'Auf dieser Seite finden Sie eine Übersicht aller :total_friends Freunde von :username auf Hypixel. Sie können ihre Ränge sehen und darauf klicken, um die Freundesliste anderer Spieler anzuzeigen.',
                "Wenn Sie <a href=':form_link'>dynamische Signaturbilder für :username generieren möchten </a>, kannst du das auch auf :site machen! Prahle mit den Minispiel oder SkyBlock-Statistiken und Erfolgen von :username auf jeder Website, auf der Sie durch <a href=':form_link'>Erstellen einer dynamischen Signatur</a> auf Bilder verlinken können."
            ],

            'tweet_text' => 'Ich stöbere durch die Freundesliste von :username auf Hypixel!',

            'loading_friends' => 'Lade Freunde ({{ meta.loaded }} / {{ meta.total_friends }})…',
            'friends_since'   => 'seit {{ new Date(player.since).toLocaleDateString() }}'
        ]
    ];
