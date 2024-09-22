<?php
    /*
 * Copyright (c) 2020-2024 Max Korlaar
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
        'title' => 'Hypixel-Gildenstatistik',

        'social' => [
            'title'       => 'Hypixel Gilden Info und Mitgliedsstatistik - :site',
            'description' => 'Finde Information zu einer Gilde auf dem Hypixel Minecraft-Server, einschließlich ihrer Stufe, Einstellungen, Beschreibung und Mitglieder. Sie können auch Gildenstatistiken für Spiele wie SkyWars, Bed Wars, TNT-Spiele, Mega Walls, Crazy Walls und Quakecraft auf :site durchsehen und vergleichen!'
        ],

        'callout'             => [
            'Schau dir Informationen zu jeder Hypixel-Gilde an, einschließlich ihrer Mitglieder, Beschreibung und sogar Spielstatistiken.',
            'Ganz einfach – Sie müssen nur einen Benutzernamen oder einen Gildennamen eingeben, um loszulegen.'
        ],
        'callout_placeholder' => 'Geben Sie Ihren Benutzernamen ein, um zu starten',
        'callout_button'      => 'Gilde anzeigen',

        'recently_viewed'                   => 'Zuletzt angesehen',
        'recently_viewed_members_and_views' => ':count Mitglieder – :views Aufrufe',

        'info' => [
            'title' => ':name Gilde',

            'social' => [
                'title' => ':name - :site',

                'default_description' => ':name ist eine Gilde auf dem Hypixel Minecraft Server',
                'description' => ' - Schau dir :name Mitglieder, Gildenstatistiken und sogar Gildenspielstatistiken für SkyWars, Bed Wars und weitere Spiele an, auf :site'
            ],

            'members'               => 'Mitgliederliste',
            'general_statistics'    => 'Allgemeine Statistiken',
            'skywars_statistics'    => 'SkyWars Statistiken',
            'bedwars_statistics'    => 'Bed Wars Statistiken',
            'tntgames_statistics'   => 'TNT-Games Statistiken',
            'megawalls_statistics'  => 'Mega Walls Statistiken',
            'crazywalls_statistics' => 'Crazy Walls Statistiken',
            'quakecraft_statistics' => 'Quakecraft Statistiken',

            'current_members'   => 'Aktuelle Mitgliederzahl',
            'level'             => 'Level',
            'tag'               => 'Etikett',
            'description'       => 'Beschreibung',
            'not_set'           => '<i>Nich gesetzt</i>',
            'guildmaster'       => 'Gildenmeister',
            'created_on'        => 'erstellt am',
            'preferred_games'   => 'Lieblingsspiel',
            'most_active_games' => 'Die meisten aktive Spiele',
            'experience'        => 'Gildenerfahrung',
            'coins'             => 'Münzen',
            'legacy_rank'       => 'Legacy-Rang',
            'joinable'          => 'Öffentlich eintretbar',
            'yes'               => 'Ja',
            'no'                => 'Nein',
            'listed'            => 'Öffentlich gelistet',

            'click_to_sort' => 'Sie können auf die Tabellenüberschriften klicken, um die Gildenmitglieder nach bestimmten Statistiken zu sortieren. Wenn Sie die genaue Position eines Spielers für eine Gilden-Bestenliste wissen möchten, bewegen Sie den Mauszeiger über den Benutzernamen, um die relative Position für die aktuell sortierte Statistik anzuzeigen!'
        ],

        'members' => [
            'page_title' => ':name Gildenmitglieder',
            'title'      => "<a href=':link'>:name</a>s Mitglieder",

            'social' => [
                'title'       => 'Gildenmitglieder von :name - :site',
                'description' => 'Alle anzeigen: :name :count mitglied und mehr auf :site'
            ],

            'loading_members' => 'Mitglieder laden ({{ meta.loaded }} / {{ meta.total_members }})…',
        ],

        'games' => [
            'general'   => [
                'page_title' => ':name Allgemeine Hypixel-Statistiken',
                'title'      => "<a href=':link'>:name</a>s Allgemeine Hypixel-Statistiken",

                'social' => [
                    'title'       => ':name Allgemeine Hypixel-Statistiken - :site',
                    'description' => 'Schau dir die allgemeinen Hypixel-Statistiken der Hypixel-Gilde :name auf :site an. Sie können die Liste durchsuchen und allgemeine Statistiken wie Hypixel-Level oder Leistungspunkte von Gildenmitgliedern vergleichen.'
                ],
            ],
            'skywars'   => [
                'page_title' => ':name SkyWars-Statistiken',
                'title'      => "<a href=':link'>:name</a>s SkyWars-Statistiken",

                'social' => [
                    'title'       => ':nameSkyWars-Statistiken - :site',
                    'description' => 'Schau dir die SkyWars-Statistiken der Hypixel-Gilde :name auf :site an. Sie können die Liste durchsuchen und die SkyWars-Statistiken der Gildenmitglieder vergleichen.'
                ],
            ],
            'bedwars'   => [
                'page_title' => ':name Bed Wars-Statistiks',
                'title'      => "<a href=':link'>:name</a>s Bed Wars statistics",

                'social' => [
                    'title'       => ':name Bed Wars statistics - :site',
                    'description' => 'Schau dir die Bed Wars-Statistik der Hypixel-Gilde :name auf :site an. Sie können die Liste durchsuchen und die Bed Wars-Statistik der Gildenmitglieder vergleichen'
                ],
            ],
            'tntgames'  => [
                'page_title' => ':name TNT-Games-Statistiken',
                'title'      => "<a href=':link'>:name</a>s TNT-Games-Statistiken",

                'social' => [
                    'title'       => ':name TTNT-Games-Statistiken - :site',
                    'description' => 'Schau dir die TNT-Games-Statistiken der Hypixel-Gilde :name auf :site an. Sie können die Liste durchsuchen und die TNT-Games-Statistiken der Gildenmitglieder vergleichen.'
                ],
            ],
            'megawalls' => [
                'page_title' => ':name Mega Walls Statistiken',
                'title'      => "<a href=':link'>:name</a>s Mega Walls Statistiken",

                'social' => [
                    'title'       => ':name TNT-Games statistics - :site',
                    'description' => 'Schau dir die Mega Walls-Statistiken der Hypixel-Gilde :name auf :site an. Sie können die Liste durchsuchen und die Mega Walls-Statistiken der Gildenmitglieder vergleichen.'
                ],
            ],
        ],

    ];
