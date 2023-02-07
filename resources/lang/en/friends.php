<?php
    /*
 * Copyright (c) 2020-2023 Max Korlaar
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
        'title' => 'Hypixel Friends List',

        'callout'             => [
            "View a list of your or someone else's friends on Hypixel, how long you've been friends with them and more.",
            "It's easy – You only have to enter your username to get started."
        ],
        'callout_placeholder' => 'Enter your username to get started',
        'callout_button'      => 'View friends',

        'recently_viewed'                   => 'Recently viewed',
        'recently_viewed_friends_and_views' => ':count friends – :views views',

        'list' => [
            'page_title' => ":username's Hypixel Friends List",
            'title'      => ":username's friends on Hypixel",

            'social' => [
                'title'       => ":username's friends on Hypixel - :site",
                'description' => 'View all of :total_friends friends of :username on the Hypixel Minecraft server. You can browse through the list and navigate to other Hypixel players.',
            ],

            'description' => [
                "On this page you can find an overview of all of :username's :total_friends friends on Hypixel. You can see their ranks and click on them to view friends lists of other players.",
                "If you want to <a href=':form_link'>generate dynamic signature images for :username</a>, you can also do that on :site! Show off :username's minigame or SkyBlock statistics and achievements on any website that allows you to link to images by <a href=':form_link'>creating a dynamic signature</a>."
            ],

            'tweet_text' => "I'm browsing :username's friend list on Hypixel!",

            'loading_friends' => 'Loading friends ({{ meta.loaded }} / {{ meta.total_friends }})…',
            'friends_since'   => 'since {{ new Date(player.since).toLocaleDateString() }}',
        ],
        'api_disabled_warning' => 'Unfortunately, citing privacy concerns, Hypixel does not allow users to look up players\' friends list via their API anymore. As a result of this change the friends list browser on Hypixel.Paniek.de is disabled.'
    ];
