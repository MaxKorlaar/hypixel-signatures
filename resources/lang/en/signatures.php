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
        'title' => 'Hypixel Dynamic Statistic Signatures',

        'groups' => [
            'generic'  => [
                'name'        => 'Generic Signatures',
                'short_name'  => 'Generic',
                'description' => 'Generic statistics for the most popular Hypixel games or your Hypixel profile.',
            ],
            'skywars'  => [
                'name'        => 'SkyWars signatures',
                'short_name'  => 'SkyWars',
                'description' => 'Statistics for SkyWars on Hypixel, such as the amount of wins, your SkyWars level and your kill/death ratio.'
            ],
            'guild'    => [
                'name'        => 'Guild Signatures',
                'short_name'  => 'Guild',
                'description' => 'Generic statistics for the guild you\'re part of on Hypixel. The general guild statistics image also shows the guild\'s banner if they have one!',
            ],
            'skyblock' => [
                'name'        => 'SkyBlock Signatures',
                'short_name'  => 'SkyBlock',
                'description' => 'Hypixel SkyBlock statistics, custom made to parse all SkyBlock data per SkyBlock profile on your account!',
            ]
        ],

        'username_or_uuid' => 'Minecraft username or UUID',
        'username_help'    => 'Enter your Minecraft username or UUID here in order to allow us to look up your statistics via the Hypixel public API. If you do not know what your UUID is, enter your username and it will be looked up for you.',

        'skyblock' => [
            'no_profiles'      => "You don't seem to have any SkyBlock profiles on your account. Please play SkyBlock and try again later, or enable your API statistics if you've disabled them.",
            'select_a_profile' => 'Please select a SkyBlock profile to use for generating your signatures.',
            'profile'          => 'SkyBlock profile',
        ],

        'your_signature'      => 'Your signature',
        'your_signature_text' => [
            'Below you can find the signature that you have selected. You can refer to it using the URL of the image, or copy and paste the BBcode on the forums in a forum post, or in your signature.',
            "It's important to use the BBcode or image URL below to refer to your signature. If you download your signature, or copy and paste the image itself, then it won't automatically be updated."
        ],

        'bbcode'              => 'BBcode',
        'bbcode_instructions' => 'Copy and paste the above snippet of text in <a target="_blank" href="https://hypixel.net/account/signature">your forum signature</a> to automatically display your chosen statistics below every post you make on the forums. You can use the URL to the image to place this signature on other websites if you wish.',

        'direct_link' => 'Direct link',

        'signature_options'      => 'Signature options',
        'signature_options_text' => 'This signature offers options that you may set to alter the looks of it:',
    ];
