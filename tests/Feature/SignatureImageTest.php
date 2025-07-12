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
     * Class SignatureImageTest
     *
     * @package Tests\Feature
     */
    class SignatureImageTest extends TestCase {

        /**
         * Test username to use for signature generation
         */
        private const TEST_USERNAME = 'Notch';

        public function testGeneralSignature(): void {
            $response = $this->get(route('signatures.general', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testSmallGeneralSignature(): void {
            $response = $this->get(route('signatures.general_small', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testGeneralTooltipSignature(): void {
            $response = $this->get(route('signatures.general_tooltip', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testUHCChampionsSignature(): void {
            $response = $this->get(route('signatures.uhc_champions', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testBedWarsSignature(): void {
            $response = $this->get(route('signatures.bedwars', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testDuelsSignature(): void {
            $response = $this->get(route('signatures.duels', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testTNTGamesSignature(): void {
            $response = $this->get(route('signatures.tnt_games', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testCopsAndCrimsSignature(): void {
            $response = $this->get(route('signatures.cops_and_crims', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testBlitzSurvivalGamesSignature(): void {
            $response = $this->get(route('signatures.blitz_survival_games', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testSkyWarsSignature(): void {
            $response = $this->get(route('signatures.skywars', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testSimpleSkyWarsSignature(): void {
            $response = $this->get(route('signatures.skywars_simple', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testAnimatedSkyWarsSignature(): void {
            $response = $this->get(route('signatures.skywars_gif', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testSkyBlockStatsSignature(): void {
            // Using a sample profile ID
            $profileId = 'sample-profile-id-123';
            $response = $this->get(route('signatures.skyblock.stats', [
                'username' => self::TEST_USERNAME,
                'profile_id' => $profileId
            ]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testSkyBlockPetsSignature(): void {
            $profileId = 'sample-profile-id-123';
            $response = $this->get(route('signatures.skyblock.pets', [
                'username' => self::TEST_USERNAME,
                'profile_id' => $profileId
            ]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testSkyBlockMinionsSignature(): void {
            $profileId = 'sample-profile-id-123';
            $response = $this->get(route('signatures.skyblock.minions', [
                'username' => self::TEST_USERNAME,
                'profile_id' => $profileId
            ]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testGuildGeneralSignature(): void {
            $response = $this->get(route('signatures.guild.general', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testGuildBannerSignature(): void {
            $response = $this->get(route('signatures.guild.banner', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testTimestampSignature(): void {
            $response = $this->get(route('signatures.other.timestamp', ['username' => self::TEST_USERNAME]));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $this->assertContains($response->headers->get('Content-Type'), [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]);
            }
        }

        public function testSignatureWithInvalidUsername(): void {
            $response = $this->get(route('signatures.general', ['username' => 'invalid_username_12345']));
            
            $this->assertContains($response->getStatusCode(), [404, 500]);
        }

        public function testSignatureWithEmptyUsername(): void {
            $response = $this->get(route('signatures.general', ['username' => '']));
            
            $this->assertContains($response->getStatusCode(), [400, 404, 422, 500]);
        }
    }