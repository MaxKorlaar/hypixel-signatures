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

    use Illuminate\Support\Str;

    return [

        /*
        |--------------------------------------------------------------------------
        | Default Session Driver
        |--------------------------------------------------------------------------
        |
        | This option controls the default session "driver" that will be used on
        | requests. By default, we will use the lightweight native driver but
        | you may specify any of the other wonderful drivers provided here.
        |
        | Supported: "file", "cookie", "database", "apc",
        |            "memcached", "redis", "dynamodb", "array"
        |
        */

        'driver' => env('SESSION_DRIVER', 'file'),

        /*
        |--------------------------------------------------------------------------
        | Session Lifetime
        |--------------------------------------------------------------------------
        |
        | Here you may specify the number of minutes that you wish the session
        | to be allowed to remain idle before it expires. If you want them
        | to immediately expire on the browser closing, set that option.
        |
        */

        'lifetime' => env('SESSION_LIFETIME', 120),

        'expire_on_close' => false,

        /*
        |--------------------------------------------------------------------------
        | Session Encryption
        |--------------------------------------------------------------------------
        |
        | This option allows you to easily specify that all of your session data
        | should be encrypted before it is stored. All encryption will be run
        | automatically by Laravel and you can use the Session like normal.
        |
        */

        'encrypt' => false,

        /*
        |--------------------------------------------------------------------------
        | Session File Location
        |--------------------------------------------------------------------------
        |
        | When using the native session driver, we need a location where session
        | files may be stored. A default has been set for you but a different
        | location may be specified. This is only needed for file sessions.
        |
        */

        'files' => storage_path('framework/sessions'),

        /*
        |--------------------------------------------------------------------------
        | Session Database Connection
        |--------------------------------------------------------------------------
        |
        | When using the "database" or "redis" session drivers, you may specify a
        | connection that should be used to manage these sessions. This should
        | correspond to a connection in your database configuration options.
        |
        */

        'connection' => env('SESSION_CONNECTION', null),

        /*
        |--------------------------------------------------------------------------
        | Session Database Table
        |--------------------------------------------------------------------------
        |
        | When using the "database" session driver, you may specify the table we
        | should use to manage the sessions. Of course, a sensible default is
        | provided for you; however, you are free to change this as needed.
        |
        */

        'table' => 'sessions',

        /*
        |--------------------------------------------------------------------------
        | Session Cache Store
        |--------------------------------------------------------------------------
        |
        | When using the "apc", "memcached", or "dynamodb" session drivers you may
        | list a cache store that should be used for these sessions. This value
        | must match with one of the application's configured cache "stores".
        |
        */

        'store' => env('SESSION_STORE', null),

        /*
        |--------------------------------------------------------------------------
        | Session Sweeping Lottery
        |--------------------------------------------------------------------------
        |
        | Some session drivers must manually sweep their storage location to get
        | rid of old sessions from storage. Here are the chances that it will
        | happen on a given request. By default, the odds are 2 out of 100.
        |
        */

        'lottery' => [2, 100],

        /*
        |--------------------------------------------------------------------------
        | Session Cookie Name
        |--------------------------------------------------------------------------
        |
        | Here you may change the name of the cookie used to identify a session
        | instance by ID. The name specified here will get used every time a
        | new session cookie is created by the framework for every driver.
        |
        */

        'cookie' => env(
            'SESSION_COOKIE',
            Str::slug(env('APP_NAME', 'laravel'), '_') . '_session'
        ),

        /*
        |--------------------------------------------------------------------------
        | Session Cookie Path
        |--------------------------------------------------------------------------
        |
        | The session cookie path determines the path for which the cookie will
        | be regarded as available. Typically, this will be the root path of
        | your application but you are free to change this when necessary.
        |
        */

        'path' => '/',

        /*
        |--------------------------------------------------------------------------
        | Session Cookie Domain
        |--------------------------------------------------------------------------
        |
        | Here you may change the domain of the cookie used to identify a session
        | in your application. This will determine which domains the cookie is
        | available to in your application. A sensible default has been set.
        |
        */

        'domain' => env('SESSION_DOMAIN', null),

        /*
        |--------------------------------------------------------------------------
        | HTTPS Only Cookies
        |--------------------------------------------------------------------------
        |
        | By setting this option to true, session cookies will only be sent back
        | to the server if the browser has a HTTPS connection. This will keep
        | the cookie from being sent to you if it can not be done securely.
        |
        */

        'secure' => env('SESSION_SECURE_COOKIE', null),

        /*
        |--------------------------------------------------------------------------
        | HTTP Access Only
        |--------------------------------------------------------------------------
        |
        | Setting this value to true will prevent JavaScript from accessing the
        | value of the cookie and the cookie will only be accessible through
        | the HTTP protocol. You are free to modify this option if needed.
        |
        */

        'http_only' => true,

        /*
        |--------------------------------------------------------------------------
        | Same-Site Cookies
        |--------------------------------------------------------------------------
        |
        | This option determines how your cookies behave when cross-site requests
        | take place, and can be used to mitigate CSRF attacks. By default, we
        | do not enable this as other CSRF protection services are in place.
        |
        | Supported: "lax", "strict"
        |
        */

        'same_site' => null,

    ];
