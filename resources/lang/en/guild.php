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
        'title' => 'Hypixel Guild Statistics',

        'social' => [
            'title'       => 'Hypixel Guild Info and Member Statistics - :site',
            'description' => 'Find information about a guild on the Hypixel Minecraft server, including their level, settings, description, members. You can also look through and compare guild statistics for games such as SkyWars, Bed Wars, TNT-Games, Mega Walls, Crazy Walls and Quakecraft, all on :site!'
        ],

        'callout'             => [
            'View information about any Hypixel Guild, including their members, description and even game statistics.',
            "It's easy – You only have to enter an username or guild name to get started."
        ],
        'callout_placeholder' => 'Enter your username to get started',
        'callout_button'      => 'View guild',

        'recently_viewed'                   => 'Recently viewed',
        'recently_viewed_members_and_views' => ':count members – :views views',

        'info' => [
            'title' => ':name Guild',

            'social' => [
                'title' => ':name - :site',

                'default_description' => ':name is a guild on the Hypixel Minecraft server',
                'description'         => " - View :name's members, guild statistics and even guild game statistics for SkyWars, Bed Wars and more games on :site"
            ],

            'members'               => 'Member list',
            'skywars_statistics'    => 'SkyWars statistics',
            'bedwars_statistics'    => 'Bed Wars statistics',
            'tntgames_statistics'   => 'TNT-Games statistics',
            'megawalls_statistics'  => 'Mega Walls statistics',
            'crazywalls_statistics' => 'Crazy Walls statistics',
            'quakecraft_statistics' => 'Quakecraft statistics',

            'current_members'   => 'Current member count',
            'level'             => 'Level',
            'tag'               => 'Tag',
            'description'       => 'Description',
            'not_set'           => '<i>None set</i>',
            'guildmaster'       => 'Guildmaster',
            'created_on'        => 'Created on',
            'preferred_games'   => 'Preferred games',
            'most_active_games' => 'Most active games',
            'experience'        => 'Guild experience',
            'coins'             => 'Coins',
            'legacy_rank'       => 'Legacy rank',
            'joinable'          => 'Publicly joinable',
            'yes'               => 'Yes',
            'no'                => 'No',
            'listed'            => 'Publicly listed',

            'click_to_sort' => 'You can click the table headers to sort the guild members by specific statistics. If you would like to know the exact position a player is on for a guild leaderboard, hover over the username in order to see their relative position for the currently sorted statistic!'
        ],

        'members' => [
            'page_title' => ':name Guild Members',
            'title'      => "<a href=':link'>:name</a>'s members",

            'social' => [
                'title'       => 'Guild members of :name - :site',
                'description' => "View all of :name's :count members and more on :site"
            ],

            'loading_members' => 'Loading members ({{ meta.loaded }} / {{ meta.total_members }})…',
        ],

        'games' => [
            'skywars'   => [
                'page_title' => ':name SkyWars statistics',
                'title'      => "<a href=':link'>:name</a>'s SkyWars statistics",

                'social' => [
                    'title'       => ':name SkyWars statistics - :site',
                    'description' => 'View the SkyWars statistics of the Hypixel guild :name on :site. You can browse through the list and compare SkyWars statistics of guild members.'
                ],
            ],
            'bedwars'   => [
                'page_title' => ':name Bed Wars statistics',
                'title'      => "<a href=':link'>:name</a>'s Bed Wars statistics",

                'social' => [
                    'title'       => ':name Bed Wars statistics - :site',
                    'description' => 'View the Bed Wars statistics of the Hypixel guild :name on :site. You can browse through the list and compare Bed Wars statistics of guild members.'
                ],
            ],
            'tntgames'  => [
                'page_title' => ':name TNT-Games statistics',
                'title'      => "<a href=':link'>:name</a>'s TNT-Games statistics",

                'social' => [
                    'title'       => ':name TNT-Games statistics - :site',
                    'description' => 'View the TNT-Games statistics of the Hypixel guild :name on :site. You can browse through the list and compare TNT-Games statistics of guild members.'
                ],
            ],
            'megawalls' => [
                'page_title' => ':name Mega Walls statistics',
                'title'      => "<a href=':link'>:name</a>'s Mega Walls statistics",

                'social' => [
                    'title'       => ':name TNT-Games statistics - :site',
                    'description' => 'View the Mega Walls statistics of the Hypixel guild :name on :site. You can browse through the list and compare Mega Walls statistics of guild members.'
                ],
            ],
        ],

    ];
