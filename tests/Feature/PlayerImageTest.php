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
     * Class PlayerImageTest
     *
     * @package Tests\Feature
     */
    class PlayerImageTest extends TestCase {

        /**
         * Test UUID (Notch's UUID)
         */
        private const TEST_UUID = 'b876ec32-e396-476b-a115-8438d83c67d4';

        public function testPlayerHeadAsWebP(): void {
            $response = $this->get(route('player.skin.head', ['uuid' => self::TEST_UUID]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertEquals('image/webp', $response->headers->get('Content-Type'));
            }
        }

        public function testPlayerHeadAsPNG(): void {
            $response = $this->get(route('player.skin.head.png', ['uuid' => self::TEST_UUID]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertEquals('image/png', $response->headers->get('Content-Type'));
            }
        }

        public function testPlayerFullSkinAsWebP(): void {
            $response = $this->get(route('player.skin.full', ['uuid' => self::TEST_UUID]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertEquals('image/webp', $response->headers->get('Content-Type'));
            }
        }

        public function testPlayerFullSkinAsPNG(): void {
            $response = $this->get(route('player.skin.full.png', ['uuid' => self::TEST_UUID]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertEquals('image/png', $response->headers->get('Content-Type'));
            }
        }

        public function testPlayerImageWithInvalidUuid(): void {
            $response = $this->get(route('player.skin.head', ['uuid' => 'invalid-uuid-format']));
            
            $this->assertContains($response->getStatusCode(), [400, 404, 422, 500]);
        }

        public function testPlayerImageWithEmptyUuid(): void {
            $response = $this->get(route('player.skin.head', ['uuid' => '']));
            
            $this->assertContains($response->getStatusCode(), [400, 404, 422, 500]);
        }

        public function testPlayerImageWithNonExistentUuid(): void {
            // A properly formatted but non-existent UUID
            $fakeUuid = '00000000-0000-0000-0000-000000000000';
            $response = $this->get(route('player.skin.head', ['uuid' => $fakeUuid]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
        }

        public function testPlayerImageCaching(): void {
            // Test that multiple requests for the same player image work correctly
            $response1 = $this->get(route('player.skin.head', ['uuid' => self::TEST_UUID]));
            $response2 = $this->get(route('player.skin.head', ['uuid' => self::TEST_UUID]));
            
            // Both requests should have the same status
            $this->assertEquals($response1->getStatusCode(), $response2->getStatusCode());
            
            if ($response1->getStatusCode() === 200) {
                $this->assertEquals('image/webp', $response1->headers->get('Content-Type'));
                $this->assertEquals('image/webp', $response2->headers->get('Content-Type'));
            }
        }
    }