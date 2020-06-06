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
        'status' => [
            'title' => ":username's online status on Hypixel",

            'social' => [
                'title'       => ":username's online status on Hypixel - :site",
                'description' => "Check out :username's current online status on Hypixel: See if :username is currently in a lobby, playing a game or doing something else! Find this and more on :site"
            ],

            //            'callout'             => [
            //                "View a list of your or someone else's friends on Hypixel, how long you've been friends with them and more.",
            //                "It's easy – You only have to enter your username to get started."
            //            ],
            //
            //            'callout_placeholder' => 'Enter your username to get started',
            //            'callout_button'      => 'View status',
            //
            //            'recently_viewed'                   => 'Recently viewed',
            //            'recently_viewed_friends_and_views' => ':last_status – :views views',

            'error'               => 'Error',
            'api_status_disabled' => ':username has chosen to hide their current Hypixel online status from the public API.',

            'in_lobby'         => 'In a lobby',
            'in_lobby_of_game' => 'In the {{ status.game }} lobby',

            'online'         => 'Online on Hypixel',
            'online_in_game' => 'Playing {{ status.game }}',

            'mode' => 'Mode: {{ status.mode_fancy }}',

            'offline'   => 'Offline',
            'last_seen' => 'Last seen {{ last_seen }}',

            'help_text' => 'On this page you can see the current status of :username on the Hypixel Minecraft server. Although we try to show the most recent status, we do implement caching in order to optimize our site\'s performance, so it may be possible that the currently shown status may be outdated by around one minute. If someone disables their session status in the API, it is not possible for us to look up their actual status. This page automatically refreshes every 10 seconds.'
        ],
    ];
