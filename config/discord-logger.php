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

use MarvinLabs\DiscordLogger\Converters\RichRecordConverter;

    return [

        /*
         * The author of the log messages. You can set both to null to keep the Webhook author set in Discord
         */
        'from'       => [
            'name'       => env('APP_NAME', 'Discord Logger'),
            'avatar_url' => null,
        ],

        /**
         * The converter to use to turn a log record into a discord message
         *
         * Bundled converters:
         * - \MarvinLabs\DiscordLogger\Converters\SimpleRecordConverter::class
         * - \MarvinLabs\DiscordLogger\Converters\RichRecordConverter::class
         */
        'converter'  => RichRecordConverter::class,

        /**
         * If enabled, stacktraces will be attached as files. If not, stacktraces will be directly printed out in the
         * message.
         *
         * Valid values are:
         *
         * - 'smart': when stacktrace is less than 2000 characters, it is inlined with the message, else attached as file
         * - 'file': stacktrace is always attached as file
         * - 'inline': stacktrace is always inlined with the message, truncated if necessary
         */
        'stacktrace' => 'smart',

        /*
         * A set of colors to associate to the different log levels when using the `RichRecordConverter`
         */
        'colors'     => [
            'DEBUG'     => 0x607d8b,
            'INFO'      => 0x4caf50,
            'NOTICE'    => 0x2196f3,
            'WARNING'   => 0xff9800,
            'ERROR'     => 0xf44336,
            'CRITICAL'  => 0xe91e63,
            'ALERT'     => 0x673ab7,
            'EMERGENCY' => 0x9c27b0,
        ],

        /*
         * A set of emojis to associate to the different log levels. Set to null to disable an emoji for a given level
         */
        'emojis'     => [
            'DEBUG'     => ':beetle:',
            'INFO'      => ':bulb:',
            'NOTICE'    => ':wink:',
            'WARNING'   => ':flushed:',
            'ERROR'     => ':poop:',
            'CRITICAL'  => ':imp:',
            'ALERT'     => ':japanese_ogre:',
            'EMERGENCY' => ':skull:',
        ],
    ];
