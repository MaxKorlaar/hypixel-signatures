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

    use Monolog\Handler\NullHandler;
    use Monolog\Handler\StreamHandler;
    use Monolog\Handler\SyslogUdpHandler;

    return [

        /*
        |--------------------------------------------------------------------------
        | Default Log Channel
        |--------------------------------------------------------------------------
        |
        | This option defines the default log channel that gets used when writing
        | messages to the logs. The name specified in this option should match
        | one of the channels defined in the "channels" configuration array.
        |
        */

        'default' => env('LOG_CHANNEL', 'stack'),

        /*
        |--------------------------------------------------------------------------
        | Log Channels
        |--------------------------------------------------------------------------
        |
        | Here you may configure the log channels for your application. Out of
        | the box, Laravel uses the Monolog PHP logging library. This gives
        | you a variety of powerful log handlers / formatters to utilize.
        |
        | Available Drivers: "single", "daily", "slack", "syslog",
        |                    "errorlog", "monolog",
        |                    "custom", "stack"
        |
        */

        'channels' => [
            'stack' => [
                'driver'            => 'stack',
                'channels'          => ['daily', 'discord'],
                'ignore_exceptions' => false,
            ],

            'single' => [
                'driver' => 'single',
                'path'   => storage_path('logs/laravel.log'),
                'level' => env('LOG_LEVEL', 'warning')
            ],

            'daily' => [
                'driver' => 'daily',
                'path'   => storage_path('logs/laravel.log'),
                'level'  => env('LOG_LEVEL', 'warning'),
                'days'   => 14,
            ],

            'slack' => [
                'driver'   => 'slack',
                'url'      => env('LOG_SLACK_WEBHOOK_URL'),
                'username' => config('app.name') . '-bot',
                'emoji'    => ':boom:',
                'level'    => env('SLACK_LOG_LEVEL', 'warning'),
            ],

            'discord' => [
                'driver' => 'custom',
                'via'    => MarvinLabs\DiscordLogger\Logger::class,
                'level'  => env('SLACK_LOG_LEVEL', 'warning'),
                'url'    => env('LOG_DISCORD_WEBHOOK_URL'),
            ],

            'papertrail' => [
                'driver'       => 'monolog',
                'level'        => 'debug',
                'handler'      => SyslogUdpHandler::class,
                'handler_with' => [
                    'host' => env('PAPERTRAIL_URL'),
                    'port' => env('PAPERTRAIL_PORT'),
                ],
            ],

            'stderr' => [
                'driver'    => 'monolog',
                'handler'   => StreamHandler::class,
                'formatter' => env('LOG_STDERR_FORMATTER'),
                'with'      => [
                    'stream' => 'php://stderr',
                ],
            ],

            'syslog' => [
                'driver' => 'syslog',
                'level'  => 'debug',
            ],

            'errorlog' => [
                'driver' => 'errorlog',
                'level'  => 'debug',
            ],

            'null' => [
                'driver'  => 'monolog',
                'handler' => NullHandler::class,
            ],
        ],

    ];
