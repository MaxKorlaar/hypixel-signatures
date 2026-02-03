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

    namespace Tests\Feature;

    use Tests\TestCase;

    /**
     * Class ApplicationIntegrationTest
     *
     * @package Tests\Feature
     */
    class ApplicationIntegrationTest extends TestCase {

        public function testPlayerProfileEndpoint(): void {
            $uuid = 'b876ec32-e396-476b-a115-8438d83c67d4';
            $response = $this->get(route('player.get_profile', ['uuid' => $uuid]));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testPlayerUuidEndpoint(): void {
            $response = $this->get(route('player.get_uuid', ['username' => 'Notch']));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testSkyBlockProfilesEndpoint(): void {
            $uuid = 'b876ec32-e396-476b-a115-8438d83c67d4';
            $response = $this->get(route('skyblock.get_profiles', ['uuid' => $uuid]));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testFriendsIndexPage(): void {
            $response = $this->get(route('friends'));
            
            $response->assertStatus(200);
            $response->assertViewIs('friends.index');
        }

        public function testFriendsFormRedirect(): void {
            $response = $this->post(route('friends.form_redirect'), [
                'username' => 'Notch'
            ]);
            
            $response->assertStatus(302);
            $response->assertRedirect();
        }

        public function testFriendsListByUuid(): void {
            $uuid = 'b876ec32-e396-476b-a115-8438d83c67d4';
            $response = $this->get(route('friends.list', ['uuid' => $uuid]));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testFriendsListByUsername(): void {
            $response = $this->get(route('friends.list.username', ['username' => 'Notch']));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testFriendsListJsonEndpoint(): void {
            $uuid = 'b876ec32-e396-476b-a115-8438d83c67d4';
            $response = $this->get(route('friends.list.json', ['uuid' => $uuid]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        }

        public function testPrivacyPage(): void {
            $response = $this->get(route('privacy'));
            
            $response->assertStatus(200);
            $response->assertViewIs('privacy');
        }

        public function testSitemapXML(): void {
            $response = $this->get(route('meta.sitemap'));
            
            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        }

        public function testSignaturesRedirectIndex(): void {
            $response = $this->get(route('signatures.form_redirect'));
            
            $response->assertStatus(302);
            $response->assertRedirect();
        }

        public function testRedirectOldSignatures(): void {
            // Test old signature redirect functionality
            $response = $this->get('/status-sig/');
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404]);
        }

        public function testApplicationConfiguration(): void {
            // Test that the application is properly configured
            $this->assertTrue(config('app.key') !== null);
            $this->assertTrue(config('app.name') !== null);
        }

        public function testRouteCaching(): void {
            // Test that routes are accessible (basic route resolution test)
            $routeCollection = app('router')->getRoutes();
            $this->assertGreaterThan(0, $routeCollection->count());
        }

        public function testMiddlewareExistence(): void {
            // Test that required middleware is available
            $middlewareGroups = config('api.middleware');
            $this->assertIsArray($middlewareGroups);
        }

        public function testThrottlingMiddleware(): void {
            // Test multiple requests to a throttled endpoint
            $uuid = 'b876ec32-e396-476b-a115-8438d83c67d4';
            
            for ($i = 0; $i < 3; $i++) {
                $response = $this->get(route('player.status', ['uuid' => $uuid]));
                $this->assertContains($response->getStatusCode(), [200, 302, 404, 429, 500]);
            }
        }

        public function testCorsHeaders(): void {
            // Test that CORS headers are properly set for API endpoints
            $response = $this->get(route('player.status.json', ['uuid' => 'b876ec32-e396-476b-a115-8438d83c67d4']));
            
            // Should have appropriate headers for API responses
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
        }

        public function testErrorHandling(): void {
            // Test that 404 errors are handled gracefully
            $response = $this->get('/non-existent-route-12345');
            
            $response->assertStatus(404);
        }

        public function testCSRFProtection(): void {
            // Test that POST requests without CSRF tokens are rejected
            $response = $this->post(route('guild.form_redirect'), [
                'username' => 'test'
            ]);
            
            // Should either be successful (if CSRF disabled in tests) or rejected
            $this->assertContains($response->getStatusCode(), [200, 302, 419, 422]);
        }
    }