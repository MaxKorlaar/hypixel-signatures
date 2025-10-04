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

    namespace App\Http;

    use App\Http\Middleware\Authenticate;
    use App\Http\Middleware\CheckForMaintenanceMode;
    use App\Http\Middleware\EncryptCookies;
    use App\Http\Middleware\RedirectIfAuthenticated;
    use App\Http\Middleware\SetLanguage;
    use App\Http\Middleware\TrimStrings;
    use App\Http\Middleware\VerifyCsrfToken;
    use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
    use Illuminate\Auth\Middleware\Authorize;
    use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
    use Illuminate\Auth\Middleware\RequirePassword;
    use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
    use Illuminate\Foundation\Http\Kernel as HttpKernel;
    use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
    use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
    use Illuminate\Http\Middleware\SetCacheHeaders;
    use Illuminate\Routing\Middleware\SubstituteBindings;
    use Illuminate\Routing\Middleware\ThrottleRequests;
    use Illuminate\Routing\Middleware\ValidateSignature;
    use Illuminate\Session\Middleware\AuthenticateSession;
    use Illuminate\Session\Middleware\StartSession;
    use Illuminate\View\Middleware\ShareErrorsFromSession;
    use Monicahq\Cloudflare\Http\Middleware\TrustProxies;

    /**
     * Class Kernel
     *
     * @package App\Http
     */
    class Kernel extends HttpKernel {
        /**
         * The application's global HTTP middleware stack.
         *
         * These middleware are run during every request to your application.
         *
         * @var array
         */
        protected $middleware = [
            TrustProxies::class,
            CheckForMaintenanceMode::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            SetLanguage::class
        ];

        /**
         * The application's route middleware groups.
         *
         * @var array
         */
        protected $middlewareGroups = [
            'web' => [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                // \Illuminate\Session\Middleware\AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
            ],

            'static' => [
                'bindings'
            ],

            'api' => [
                'throttle:60,1',
                'bindings',
            ],
        ];

        /**
         * The application's route middleware.
         *
         * These middleware may be assigned to groups or used individually.
         *
         * @var array
         */
        protected $middlewareAliases = [
            'auth'             => Authenticate::class,
            'auth.basic'       => AuthenticateWithBasicAuth::class,
            'bindings'         => SubstituteBindings::class,
            'cache.headers'    => SetCacheHeaders::class,
            'can'              => Authorize::class,
            'guest'            => RedirectIfAuthenticated::class,
            'password.confirm' => RequirePassword::class,
            'signed'           => ValidateSignature::class,
            'throttle'         => ThrottleRequests::class,
            'verified'         => EnsureEmailIsVerified::class,
        ];

        /**
         * The priority-sorted list of middleware.
         *
         * This forces non-global middleware to always be in the given order.
         *
         * @var array
         */
        protected $middlewarePriority = [
            StartSession::class,
            ShareErrorsFromSession::class,
            Authenticate::class,
            ThrottleRequests::class,
            AuthenticateSession::class,
            SubstituteBindings::class,
            Authorize::class,
        ];
    }
