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
        | Default Mailer
        |--------------------------------------------------------------------------
        |
        | This option controls the default mailer that is used to send any email
        | messages sent by your application. Alternative mailers may be setup
        | and used as needed; however, this mailer will be used by default.
        |
        */

        'default' => env('MAIL_MAILER', 'smtp'),

        /*
        |--------------------------------------------------------------------------
        | Mailer Configurations
        |--------------------------------------------------------------------------
        |
        | Here you may configure all of the mailers used by your application plus
        | their respective settings. Several examples have been configured for
        | you and you are free to add your own as your application requires.
        |
        | Laravel supports a variety of mail "transport" drivers to be used while
        | sending an e-mail. You will specify which one you are using for your
        | mailers below. You are free to add additional mailers as required.
        |
        | Supported: "smtp", "sendmail", "mailgun", "ses",
        |            "postmark", "log", "array"
        |
        */

        'mailers' => [
            'smtp' => [
                'transport'  => 'smtp',
                'host'       => env('MAIL_HOST', 'smtp.mailgun.org'),
                'port'       => env('MAIL_PORT', 587),
                'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'username'   => env('MAIL_USERNAME'),
                'password'   => env('MAIL_PASSWORD'),
                'timeout'    => null,
            ],

            'ses' => [
                'transport' => 'ses',
            ],

            'mailgun' => [
                'transport' => 'mailgun',
            ],

            'postmark' => [
                'transport' => 'postmark',
            ],

            'sendmail' => [
                'transport' => 'sendmail',
                'path'      => '/usr/sbin/sendmail -bs',
            ],

            'log' => [
                'transport' => 'log',
                'channel'   => env('MAIL_LOG_CHANNEL'),
            ],

            'array' => [
                'transport' => 'array',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Global "From" Address
        |--------------------------------------------------------------------------
        |
        | You may wish for all e-mails sent by your application to be sent from
        | the same address. Here, you may specify a name and address that is
        | used globally for all e-mails that are sent by your application.
        |
        */

        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'name'    => env('MAIL_FROM_NAME', 'Example'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Markdown Mail Settings
        |--------------------------------------------------------------------------
        |
        | If you are using Markdown based email rendering, you may configure your
        | theme and component paths here, allowing you to customize the design
        | of the emails. Or, you may simply stick with the Laravel defaults!
        |
        */

        'markdown' => [
            'theme' => 'default',

            'paths' => [
                resource_path('views/vendor/mail'),
            ],
        ],

    ];
