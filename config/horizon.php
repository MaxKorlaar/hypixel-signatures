<?php
/*
 * Copyright (c) 2021-2024 Max Korlaar
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
        | Horizon Domain
        |--------------------------------------------------------------------------
        |
        | This is the subdomain where Horizon will be accessible from. If this
        | setting is null, Horizon will reside under the same domain as the
        | application. Otherwise, this value will serve as the subdomain.
        |
        */

        'domain' => null,

        /*
        |--------------------------------------------------------------------------
        | Horizon Path
        |--------------------------------------------------------------------------
        |
        | This is the URI path where Horizon will be accessible from. Feel free
        | to change this path to anything you like. Note that the URI will not
        | affect the paths of its internal API that aren't exposed to users.
        |
        */

        'path' => 'horizon',

        /*
        |--------------------------------------------------------------------------
        | Horizon Redis Connection
        |--------------------------------------------------------------------------
        |
        | This is the name of the Redis connection where Horizon will store the
        | meta information required for it to function. It includes the list
        | of supervisors, failed jobs, job metrics, and other information.
        |
        */

        'use' => 'default',

        /*
        |--------------------------------------------------------------------------
        | Horizon Redis Prefix
        |--------------------------------------------------------------------------
        |
        | This prefix will be used when storing all Horizon data in Redis. You
        | may modify the prefix when you are running multiple installations
        | of Horizon on the same server so that they don't have problems.
        |
        */

        'prefix' => env('HORIZON_PREFIX', 'horizon:'),

        /*
        |--------------------------------------------------------------------------
        | Horizon Route Middleware
        |--------------------------------------------------------------------------
        |
        | These middleware will get attached onto each Horizon route, giving you
        | the chance to add your own middleware to this list or change any of
        | the existing middleware. Or, you can simply stick with this list.
        |
        */

        'middleware' => ['web'],

        /*
        |--------------------------------------------------------------------------
        | Queue Wait Time Thresholds
        |--------------------------------------------------------------------------
        |
        | This option allows you to configure when the LongWaitDetected event
        | will be fired. Every connection / queue combination may have its
        | own, unique threshold (in seconds) before this event is fired.
        |
        */

        'waits' => [
            'redis:default' => 60,
        ],

        /*
        |--------------------------------------------------------------------------
        | Job Trimming Times
        |--------------------------------------------------------------------------
        |
        | Here you can configure for how long (in minutes) you desire Horizon to
        | persist the recent and failed jobs. Typically, recent jobs are kept
        | for one hour while all failed jobs are stored for an entire week.
        |
        */

        'trim' => [
            'recent'        => 30,
            'completed'     => 30,
            'recent_failed' => 60 * 24,
            'failed'        => 60 * 24,
            'monitored'     => 60 * 48,
        ],

        /*
        |--------------------------------------------------------------------------
        | Fast Termination
        |--------------------------------------------------------------------------
        |
        | When this option is enabled, Horizon's "terminate" command will not
        | wait on all of the workers to terminate unless the --wait option
        | is provided. Fast termination can shorten deployment delay by
        | allowing a new instance of Horizon to start while the last
        | instance will continue to terminate each of its workers.
        |
        */

        'fast_termination' => false,

        /*
        |--------------------------------------------------------------------------
        | Memory Limit (MB)
        |--------------------------------------------------------------------------
        |
        | This value describes the maximum amount of memory the Horizon worker
        | may consume before it is terminated and restarted. You should set
        | this value according to the resources available to your server.
        |
        */

        'memory_limit' => 128,

        /*
        |--------------------------------------------------------------------------
        | Queue Worker Configuration
        |--------------------------------------------------------------------------
        |
        | Here you may define the queue worker settings used by your application
        | in all environments. These supervisors and settings handle all your
        | queued jobs and will be provisioned by Horizon during deployment.
        |
        */

        'environments' => [
            'production' => [
                'supervisor-1' => [
                    'connection'  => 'redis',
                    'queue'       => ['default'],
                    'balance'     => 'auto',
                    'processes'   => 1,
                    'tries'       => 5,
                    'retry_after' => 120,
                    'timeout'     => 60
                ],
                'supervisor-2' => [
                    'connection'  => 'redis',
                    'queue'       => ['hypixel-api'],
                    'balance'     => 'auto',
                    'processes'   => 20,
                    'tries'       => 1,
                    'retry_after' => 60,
                    'timeout'     => 15
                ],
            ],

            'local' => [
                'supervisor-1' => [
                    'connection'  => 'redis',
                    'queue'       => ['default'],
                    'balance'     => 'auto',
                    'processes'   => 3,
                    'tries'       => 5,
                    'retry_after' => 60,
                    'timeout'     => 30
                ],
                'supervisor-2' => [
                    'connection'  => 'redis',
                    'queue'       => ['hypixel-api'],
                    'balance'     => 'auto',
                    'processes'   => 20,
                    'tries'       => 5,
                    'retry_after' => 60,
                    'timeout' => 15
                ],
            ],
        ],

        'production_allow_ip' => env('HORIZON_ALLOW_IP', false)
    ];
