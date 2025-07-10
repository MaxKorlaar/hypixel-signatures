<?php
    /*
 * Copyright (c) 2020-2025 Max Korlaar
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

    namespace App\Providers;

    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\RateLimiter;
    use Illuminate\Support\Facades\Route;

    /**
     * Class RouteServiceProvider
     *
     * @package App\Providers
     */
    class RouteServiceProvider extends ServiceProvider {
        /**
         * This namespace is applied to your controller routes.
         *
         * In addition, it is set as the URL generator's root namespace.
         *
         * @var string
         */
        protected $namespace = 'App\Http\Controllers';

        /**
         * Define your route model bindings, pattern filters, etc.
         *
         * @return void
         */
        public function boot(): void {
            Route::pattern('uuid', '[0-9a-f]{32}');

            $this->configureRateLimiting();

            parent::boot();
        }

        /**
         * Configure the rate limiters for the application.
         *
         * @return void
         */
        protected function configureRateLimiting(): void {
            RateLimiter::for('player-status', static function (Request $request) {
                return Limit::perHour(120)->by($request->ip())->response(function () {
                    return response()->view('errors.429', ['retry_after' => 3600], 429);
                });
            });

            RateLimiter::for('friends', static function (Request $request) {
                return Limit::perHour(200)->by($request->ip())->response(function () {
                    return response()->view('errors.429', ['retry_after' => 3600], 429);
                });
            });

            RateLimiter::for('guild', static function (Request $request) {
                return Limit::perHour(400)->by($request->ip())->response(function () {
                    return response()->view('errors.429', ['retry_after' => 3600], 429);
                });
            });

            RateLimiter::for('player', static function (Request $request) {
                return Limit::perHour(600)->by($request->ip())->response(function () {
                    return response()->view('errors.429', ['retry_after' => 3600], 429);
                });
            });
        }

        /**
         * Define the routes for the application.
         *
         * @return void
         */
        public function map(): void {
            //$this->mapApiRoutes();

            $this->mapWebRoutes();
            $this->mapStaticWebRoutes();

            //
        }

        /**
         * Define the "api" routes for the application.
         *
         * These routes are typically stateless.
         *
         * @return void
         */
        protected function mapApiRoutes(): void {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));
        }

        /**
         * Define the "web" routes for the application.
         *
         * These routes all receive session state, CSRF protection, etc.
         *
         * @return void
         */
        protected function mapWebRoutes(): void {
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        }

        protected function mapStaticWebRoutes(): void {
            Route::middleware('static')
                ->namespace($this->namespace)
                ->group(base_path('routes/web_static.php'));
        }
    }
