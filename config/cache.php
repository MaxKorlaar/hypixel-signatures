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

    use Illuminate\Support\Str;

    return [

        /*
        |--------------------------------------------------------------------------
        | Default Cache Store
        |--------------------------------------------------------------------------
        |
        | This option controls the default cache connection that gets used while
        | using this caching library. This connection is used when another is
        | not explicitly specified when executing a given caching function.
        |
        | Supported: "apc", "array", "database", "file",
        |            "memcached", "redis", "dynamodb"
        |
        */

        'default' => env('CACHE_DRIVER', 'file'),

        /*
        |--------------------------------------------------------------------------
        | Cache Stores
        |--------------------------------------------------------------------------
        |
        | Here you may define all of the cache "stores" for your application as
        | well as their drivers. You may even define multiple stores for the
        | same cache driver to group types of items stored in your caches.
        |
        */

        'stores' => [

            'apc' => [
                'driver' => 'apc',
            ],

            'array' => [
                'driver' => 'array',
            ],

            'database' => [
                'driver'     => 'database',
                'table'      => 'cache',
                'connection' => null,
            ],

            'file' => [
                'driver' => 'file',
                'path'   => storage_path('framework/cache/data'),
            ],

            'memcached' => [
                'driver'        => 'memcached',
                'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
                'sasl'          => [
                    env('MEMCACHED_USERNAME'),
                    env('MEMCACHED_PASSWORD'),
                ],
                'options'       => [
                    // Memcached::OPT_CONNECT_TIMEOUT => 2000,
                ],
                'servers'       => [
                    [
                        'host'   => env('MEMCACHED_HOST', '127.0.0.1'),
                        'port'   => env('MEMCACHED_PORT', 11211),
                        'weight' => 100,
                    ],
                ],
            ],

            'redis' => [
                'driver'     => 'redis',
                'connection' => 'cache',
            ],

            'dynamodb' => [
                'driver'   => 'dynamodb',
                'key'      => env('AWS_ACCESS_KEY_ID'),
                'secret'   => env('AWS_SECRET_ACCESS_KEY'),
                'region'   => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'table'    => env('DYNAMODB_CACHE_TABLE', 'cache'),
                'endpoint' => env('DYNAMODB_ENDPOINT'),
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Key Prefix
        |--------------------------------------------------------------------------
        |
        | When utilizing a RAM based store such as APC or Memcached, there might
        | be other applications utilizing the same cache. So, we'll specify a
        | value to get prefixed to all our keys so we can avoid collisions.
        |
        */

        'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_cache'),

        'times' => [
            'mojang_api' => 86400 // 1 day
        ],

    ];
