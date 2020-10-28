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
        'title' => 'Hypixel Dynamic Statistic Sygnatur',

        'groups' => [
            'generic'  => [
                'name'        => 'Allgemeine Signatur',
                'short_name'  => 'Allgemein',
                'description' => 'Allgemeine Statistiken für die beliebtesten Hypixel-Spiele oder Ihr Hypixel-Profil.',
            ],
            'skywars'  => [
                'name'        => 'SkyWars Signatur',
                'short_name'  => 'SkyWars',
                'description' => 'Statistiken für SkyWars auf Hypixel, zum Beispiel die Anzahl gewonnener Runder, dein SkyWars Level und deiner Tötungen/Todesfälle Verhältnis.'
            ],
            'guild'    => [
                'name'        => 'Gilden Signatur',
                'short_name'  => 'Gilde',
                'description' => 'Statistiken für die Gilde, der du bei Hypixel angehörst. Das allgemeine Gildenstatistikbild zeigt auch das Banner der Gilde, falls vorhanden!',
            ],
            'skyblock' => [
                'name'        => 'SkyBlock Sygnatur',
                'short_name'  => 'SkyBlock',
                'description' => 'Hypixel SkyBlock-Statistiken, maßgeschneidert, um alle SkyBlock-Daten pro SkyBlock-Profil in Ihrem Konto zu analysieren!',
            ]
        ],

        'username_or_uuid' => 'Minecraft-Benutzername oder UUID',
        'username_help'    => 'Geben Sie hier Ihren Minecraft-Benutzernamen oder Ihre UUID ein, damit wir Ihre Statistiken über die öffentliche Hypixel-API abrufen können. Wenn Sie Ihre UUID nicht kennen, geben Sie Ihren Benutzernamen ein und er wird für Sie nachgeschlagen.',

        'skyblock' => [
            'no_profiles'      => 'Sie scheinen keine SkyBlock-Profile in Ihrem Konto zu haben. Bitte spielen Sie SkyBlock und versuchen Sie es später erneut oder aktivieren Sie Ihre API-Statistiken, wenn Sie sie deaktiviert haben.',
            'select_a_profile' => 'Bitte wählen Sie ein SkyBlock-Profil aus, das zum Generieren Ihrer Sygnatur verwendet werden soll.',
            'profile'          => 'SkyBlock profile',
        ],

        'your_signature'      => 'Deine Signatur',
        'your_signature_text' => [
            'Unten finden Sie die von Ihnen ausgewählte Signatur. Sie können über die URL des Bildes darauf verweisen oder den BBcode kopieren und in die Foren eines Forumsbeitrags oder in Ihre Unterschrift einfügen. ',
            'Es ist wichtig, den unten stehenden BBcode oder die Bild-URL zu verwenden, um auf Ihre Signatur zu verweisen. Wenn Sie Ihre Signatur herunterladen oder das Bild selbst kopieren und einfügen, wird es nicht automatisch aktualisiert.'
        ],

        'bbcode'              => 'BBcode',
        'bbcode_instructions' => 'Kopieren Sie den obigen Textausschnitt und fügen Sie ihn in <a target="_blank" href="https://hypixel.net/account/signature">Ihrer Forensignatur</a> ein, um Ihre ausgewählten Statistiken automatisch unter jedem von Ihnen verfassten Beitrag anzuzeigen in den Foren. Sie können die URL zum Bild verwenden, um diese Signatur auf anderen Websites zu platzieren, wenn Sie dies wünschen.',

        'direct_link' => 'Direkte Verbindung',

        'signature_options'      => 'Signatur Optionen',
        'signature_options_text' => 'Diese Signatur bietet Optionen an, mit denen Sie das Aussehen ändern können:',
    ];
