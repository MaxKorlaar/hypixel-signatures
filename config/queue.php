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

    return [

        /*
        |--------------------------------------------------------------------------
        | Default Queue Connection Name
        |--------------------------------------------------------------------------
        |
        | Laravel's queue API supports an assortment of back-ends via a single
        | API, giving you convenient access to each back-end using the same
        | syntax for every one. Here you may define a default connection.
        |
        */

        'default' => env('QUEUE_CONNECTION', 'sync'),

        /*
        |--------------------------------------------------------------------------
        | Queue Connections
        |--------------------------------------------------------------------------
        |
        | Here you may configure the connection information for each server that
        | is used by your application. A default configuration has been added
        | for each back-end shipped with Laravel. You are free to add more.
        |
        | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
        |
        */

        'connections' => [

            'sync' => [
                'driver' => 'sync',
            ],

            'database' => [
                'driver'      => 'database',
                'table'       => 'jobs',
                'queue'       => 'default',
                'retry_after' => 90,
            ],

            'beanstalkd' => [
                'driver'      => 'beanstalkd',
                'host'        => 'localhost',
                'queue'       => 'default',
                'retry_after' => 90,
                'block_for'   => 0,
            ],

            'sqs' => [
                'driver' => 'sqs',
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
                'queue'  => env('SQS_QUEUE', 'your-queue-name'),
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            ],

            'redis' => [
                'driver'      => 'redis',
                'connection'  => 'default',
                'queue'       => env('REDIS_QUEUE', 'default'),
                'retry_after' => 90,
                'block_for'   => null,
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Failed Queue Jobs
        |--------------------------------------------------------------------------
        |
        | These options configure the behavior of failed queue job logging so you
        | can control which database and table are used to store the jobs that
        | have failed. You may change them to any database / table you wish.
        |
        */

        'failed' => [
            'driver'   => env('QUEUE_FAILED_DRIVER', 'database'),
            'database' => env('DB_CONNECTION', 'mysql'),
            'table'    => 'failed_jobs',
        ],

    ];
