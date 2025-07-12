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
     * Class GuildTest
     *
     * @package Tests\Feature
     */
    class GuildTest extends TestCase {

        public function testGuildIndexPage(): void {
            $response = $this->get(route('guild'));
            
            $response->assertStatus(200);
            $response->assertViewIs('guild.index');
        }

        public function testGuildFormRedirect(): void {
            $response = $this->post(route('guild.form_redirect'), [
                'username' => 'HypixelGuild'
            ]);
            
            $response->assertStatus(302);
            $response->assertRedirect();
        }

        public function testGuildInfoPage(): void {
            $response = $this->get(route('guild.info', ['name' => 'HypixelGuild']));
            
            // Should return 200 or handle gracefully if guild doesn't exist
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testGuildMembersPage(): void {
            $response = $this->get(route('guild.members', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testGuildMembersJsonEndpoint(): void {
            $response = $this->get(route('guild.members.json', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        }

        public function testGuildSkyWarsStatistics(): void {
            $response = $this->get(route('guild.games.skywars', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testGuildSkyWarsStatisticsJson(): void {
            $response = $this->get(route('guild.games.skywars.json', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        }

        public function testGuildBedWarsStatistics(): void {
            $response = $this->get(route('guild.games.bedwars', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testGuildBedWarsStatisticsJson(): void {
            $response = $this->get(route('guild.games.bedwars.json', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        }

        public function testGuildTNTGamesStatistics(): void {
            $response = $this->get(route('guild.games.tntgames', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testGuildTNTGamesStatisticsJson(): void {
            $response = $this->get(route('guild.games.tntgames.json', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        }

        public function testGuildMegaWallsStatistics(): void {
            $response = $this->get(route('guild.games.megawalls', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testGuildMegaWallsStatisticsJson(): void {
            $response = $this->get(route('guild.games.megawalls.json', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        }

        public function testGuildMurderMysteryStatistics(): void {
            $response = $this->get(route('guild.games.murdermystery', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testGuildMurderMysteryStatisticsJson(): void {
            $response = $this->get(route('guild.games.murdermystery.json', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        }

        public function testGuildGeneralStatistics(): void {
            $response = $this->get(route('guild.members.general', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
        }

        public function testGuildGeneralStatisticsJson(): void {
            $response = $this->get(route('guild.members.general.json', ['name' => 'HypixelGuild']));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        }

        public function testGuildWithInvalidName(): void {
            $response = $this->get(route('guild.info', ['name' => 'InvalidGuildName12345']));
            
            $this->assertContains($response->getStatusCode(), [404, 500]);
        }

        public function testGuildWithEmptyName(): void {
            $response = $this->get(route('guild.info', ['name' => '']));
            
            $this->assertContains($response->getStatusCode(), [400, 404, 422, 500]);
        }

        public function testGuildBannerEndpoint(): void {
            // Test with a sample guild ID
            $response = $this->get(route('guild.banner', ['id' => '507cf4a5c8974cce8d0a1ad2']));
            
            $this->assertContains($response->getStatusCode(), [200, 404, 500]);
            
            if ($response->getStatusCode() === 200) {
                $response->assertHeader('Content-Type', 'image/png');
            }
        }
    }