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

    namespace Tests\Unit;

    use Tests\TestCase;

    /**
     * Class UtilitiesTest
     *
     * @package Tests\Unit
     */
    class UtilitiesTest extends TestCase {

        public function testUuidValidation(): void {
            // Test valid UUID format
            $validUuid = 'b876ec32-e396-476b-a115-8438d83c67d4';
            $this->assertTrue($this->isValidUuid($validUuid));

            // Test invalid UUID format
            $invalidUuid = 'invalid-uuid';
            $this->assertFalse($this->isValidUuid($invalidUuid));

            // Test empty UUID
            $this->assertFalse($this->isValidUuid(''));

            // Test null UUID
            $this->assertFalse($this->isValidUuid(null));
        }

        public function testUsernameValidation(): void {
            // Test valid usernames
            $validUsernames = ['Notch', 'Test123', 'Player_Name', 'user'];
            foreach ($validUsernames as $username) {
                $this->assertTrue($this->isValidUsername($username));
            }

            // Test invalid usernames
            $invalidUsernames = ['', 'toolongusernamethatexceedslimit', '123', 'user!', 'user@'];
            foreach ($invalidUsernames as $username) {
                $this->assertFalse($this->isValidUsername($username));
            }
        }

        public function testConfigurationValues(): void {
            // Test that essential configuration values are set
            $this->assertNotNull(config('app.key'));
            $this->assertNotNull(config('app.name'));
            $this->assertNotNull(config('app.env'));
        }

        public function testEnvironmentConfiguration(): void {
            // Test that we're in testing environment
            $this->assertEquals('testing', config('app.env'));
            
            // Test cache configuration for testing
            $this->assertEquals('array', config('cache.default'));
        }

        public function testRoutesConfiguration(): void {
            // Test that routes are properly loaded
            $router = app('router');
            $routes = $router->getRoutes();
            
            $this->assertGreaterThan(0, $routes->count());
            
            // Test that essential routes exist
            $this->assertTrue($routes->hasNamedRoute('home'));
            $this->assertTrue($routes->hasNamedRoute('guild'));
            $this->assertTrue($routes->hasNamedRoute('friends'));
            $this->assertTrue($routes->hasNamedRoute('signatures'));
        }

        public function testMiddlewareRegistration(): void {
            // Test that middleware is properly registered
            $router = app('router');
            $middlewareGroups = $router->getMiddlewareGroups();
            
            $this->assertArrayHasKey('web', $middlewareGroups);
            $this->assertArrayHasKey('api', $middlewareGroups);
        }

        public function testServiceProviders(): void {
            // Test that essential service providers are loaded
            $app = app();
            
            $this->assertTrue($app->providerIsLoaded(\Illuminate\Foundation\Providers\FoundationServiceProvider::class));
            $this->assertTrue($app->providerIsLoaded(\Illuminate\View\ViewServiceProvider::class));
            $this->assertTrue($app->providerIsLoaded(\Illuminate\Routing\RoutingServiceProvider::class));
        }

        public function testDatabaseConfiguration(): void {
            // Test database configuration for testing
            $defaultConnection = config('database.default');
            $this->assertNotNull($defaultConnection);
            
            $connections = config('database.connections');
            $this->assertIsArray($connections);
            $this->assertArrayHasKey($defaultConnection, $connections);
        }

        public function testCacheConfiguration(): void {
            // Test cache configuration
            $cacheDriver = config('cache.default');
            $this->assertEquals('array', $cacheDriver);
            
            $stores = config('cache.stores');
            $this->assertIsArray($stores);
            $this->assertArrayHasKey('array', $stores);
        }

        public function testSessionConfiguration(): void {
            // Test session configuration for testing
            $sessionDriver = config('session.driver');
            $this->assertEquals('array', $sessionDriver);
        }

        public function testQueueConfiguration(): void {
            // Test queue configuration for testing
            $queueConnection = config('queue.default');
            $this->assertEquals('sync', $queueConnection);
        }

        /**
         * Helper method to validate UUID format
         */
        private function isValidUuid(?string $uuid): bool {
            if ($uuid === null) {
                return false;
            }
            
            $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
            return preg_match($pattern, $uuid) === 1;
        }

        /**
         * Helper method to validate username format
         */
        private function isValidUsername(?string $username): bool {
            if ($username === null || $username === '') {
                return false;
            }
            
            // Minecraft username rules: 3-16 characters, alphanumeric and underscore only
            $pattern = '/^[a-zA-Z0-9_]{3,16}$/';
            return preg_match($pattern, $username) === 1;
        }
    }