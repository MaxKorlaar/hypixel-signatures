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
        'title' => 'Hypixel Vriendenlijst',

        'callout'             => [
            'Bekijk de vriendenlijst van een speler of jezelf op Hypixel, hoe lang je al bevriend met ze bent en meer',
            'Het is gemakkelijk – Je hoeft alleen je gebruikersnaam in te voeren om te beginnen.'
        ],
        'callout_placeholder' => 'Voer je gebruikersnaam in om te beginnen',
        'callout_button'      => 'Bekijk vrienden',

        'recently_viewed'                   => 'Recent bekeken',
        'recently_viewed_friends_and_views' => ':count vrienden – :views keer bekeken',

        'list' => [
            'page_title' => ':username Hypixel Vriendenlijst',
            'title'      => 'De vrienden van :username op Hypixel',

            'social' => [
                'title'       => 'De vrienden van :username op Hypixel - :site',
                'description' => 'Bekijk alle :total_friends vrienden van :username op de Hypixel Minecraft-server. Je kan door de lijst bladeren en naar andere Hypixel-spelers kijken.',
            ],

            'description' => [
                'Op deze pagina zie je een overzicht van alle vrienden van :username op Hypixel. Je kan hun ranks zien en op ze klikken om de vriendenlijsten van andere spelers te bekijken.',
                "Als je een <a href=':form_link'>dynamische afbeelding voor :username wil genereren</a>, dat kan je ook doen op :site! Pronk met de prestaties, minigame- of SkyBlock-statistieken van :username op elke website die het je toestaat om naar afbeeldingen te verwijzen door <a href=':form_link'>een dynamische afbeelding aan te maken</a>."
            ],

            'tweet_text' => 'Ik bekijk de vriendenlijst van :username op Hypixel!',

            'loading_friends' => 'Vrienden laden ({{ meta.loaded }} / {{ meta.total_friends }})…',
            'friends_since'   => 'sinds {{ new Date(player.since).toLocaleDateString() }}'
        ]
    ];
