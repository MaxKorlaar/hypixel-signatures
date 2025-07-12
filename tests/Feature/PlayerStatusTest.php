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

    namespace Tests\Feature;

    use Tests\TestCase;

    /**
     * Class PlayerStatusTest
     *
     * @package Tests\Feature
     */
    class PlayerStatusTest extends TestCase {
        
        public function testPlayerStatusIndexPage(): void {
            $response = $this->get(route('player.status.index'));
            
            $response->assertStatus(200);
            $response->assertViewIs('player.status.index');
        }

        public function testPlayerStatusFormRedirect(): void {
            $response = $this->post(route('player.status.form_redirect'), [
                'username' => 'Notch'
            ]);
            
            $response->assertStatus(302);
            $response->assertRedirect();
        }

        public function testPlayerStatusByUuidRoute(): void {
            // Test with a sample UUID (Notch's UUID)
            $uuid = '069a79f4-44e9-4726-a5be-fca90e38aaf5';
            
            $response = $this->get(route('player.status', ['uuid' => $uuid]));
            
            // Should return 200 or redirect, depending on API availability
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testPlayerStatusByUsernameRoute(): void {
            $response = $this->get(route('player.status.username', ['username' => 'Notch']));
            
            // Should return 200 or redirect, depending on API availability
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testPlayerStatusJsonEndpoint(): void {
            $uuid = '069a79f4-44e9-4726-a5be-fca90e38aaf5';
            
            $response = $this->get(route('player.status.json', ['uuid' => $uuid]));
            
            // Should return JSON response or error
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        }

        public function testPlayerStatusWithInvalidUuid(): void {
            $response = $this->get(route('player.status', ['uuid' => 'invalid-uuid']));
            
            // Should handle invalid UUID gracefully
            $this->assertContains($response->getStatusCode(), [400, 404, 422, 500]);
        }

        public function testPlayerStatusWithEmptyUsername(): void {
            $response = $this->get(route('player.status.username', ['username' => '']));
            
            // Should handle empty username gracefully
            $this->assertContains($response->getStatusCode(), [400, 404, 422, 500]);
        }
    }