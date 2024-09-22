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

        /*
        |--------------------------------------------------------------------------
        | Default Broadcaster
        |--------------------------------------------------------------------------
        |
        | This option controls the default broadcaster that will be used by the
        | framework when an event needs to be broadcast. You may set this to
        | any of the connections defined in the "connections" array below.
        |
        | Supported: "pusher", "redis", "log", "null"
        |
        */

        'default' => env('BROADCAST_DRIVER', 'null'),

        /*
        |--------------------------------------------------------------------------
        | Broadcast Connections
        |--------------------------------------------------------------------------
        |
        | Here you may define all of the broadcast connections that will be used
        | to broadcast events to other systems or over websockets. Samples of
        | each available type of connection are provided inside this array.
        |
        */

        'connections' => [

            'pusher' => [
                'driver'  => 'pusher',
                'key'     => env('PUSHER_APP_KEY'),
                'secret'  => env('PUSHER_APP_SECRET'),
                'app_id'  => env('PUSHER_APP_ID'),
                'options' => [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS'  => true,
                ],
            ],

            'redis' => [
                'driver'     => 'redis',
                'connection' => 'default',
            ],

            'log' => [
                'driver' => 'log',
            ],

            'null' => [
                'driver' => 'null',
            ],

        ],

    ];
