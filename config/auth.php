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
        | Authentication Defaults
        |--------------------------------------------------------------------------
        |
        | This option controls the default authentication "guard" and password
        | reset options for your application. You may change these defaults
        | as required, but they're a perfect start for most applications.
        |
        */

        'defaults' => [
            'guard'     => 'web',
            'passwords' => 'users',
        ],

        /*
        |--------------------------------------------------------------------------
        | Authentication Guards
        |--------------------------------------------------------------------------
        |
        | Next, you may define every authentication guard for your application.
        | Of course, a great default configuration has been defined for you
        | here which uses session storage and the Eloquent user provider.
        |
        | All authentication drivers have a user provider. This defines how the
        | users are actually retrieved out of your database or other storage
        | mechanisms used by this application to persist your user's data.
        |
        | Supported: "session", "token"
        |
        */

        'guards' => [
            'web' => [
                'driver'   => 'session',
                'provider' => 'users',
            ],

            'api' => [
                'driver'   => 'token',
                'provider' => 'users',
                'hash'     => false,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | User Providers
        |--------------------------------------------------------------------------
        |
        | All authentication drivers have a user provider. This defines how the
        | users are actually retrieved out of your database or other storage
        | mechanisms used by this application to persist your user's data.
        |
        | If you have multiple user tables or models you may configure multiple
        | sources which represent each model / table. These sources may then
        | be assigned to any extra authentication guards you have defined.
        |
        | Supported: "database", "eloquent"
        |
        */

        'providers' => [
            'users' => [
                'driver' => 'eloquent',
                'model'  => App\User::class,
            ],

            // 'users' => [
            //     'driver' => 'database',
            //     'table' => 'users',
            // ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Resetting Passwords
        |--------------------------------------------------------------------------
        |
        | You may specify multiple password reset configurations if you have more
        | than one user table or model in the application and you want to have
        | separate password reset settings based on the specific user types.
        |
        | The expire time is the number of minutes that the reset token should be
        | considered valid. This security feature keeps tokens short-lived so
        | they have less time to be guessed. You may change this as needed.
        |
        */

        'passwords' => [
            'users' => [
                'provider' => 'users',
                'table'    => 'password_resets',
                'expire'   => 60,
                'throttle' => 60,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Password Confirmation Timeout
        |--------------------------------------------------------------------------
        |
        | Here you may define the amount of seconds before a password confirmation
        | times out and the user is prompted to re-enter their password via the
        | confirmation screen. By default, the timeout lasts for three hours.
        |
        */

        'password_timeout' => 10800,

    ];
