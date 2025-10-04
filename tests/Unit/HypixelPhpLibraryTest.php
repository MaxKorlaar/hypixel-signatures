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

    namespace Tests\Unit;

    use Plancke\HypixelPHP\classes\serverType\ServerTypes;
    use Plancke\HypixelPHP\fetch\Fetcher;
    use Tests\TestCase;

    /**
     * Test that the hypixel-php library is properly configured
     * after upgrading from the fork to upstream v1.5
     *
     * @package Tests\Unit
     */
    class HypixelPhpLibraryTest extends TestCase {
        public function testServerTypesClassExists(): void {
            $this->assertTrue(class_exists(ServerTypes::class), 'ServerTypes class should exist');
        }

        public function testServerTypesConstants(): void {
            $this->assertEquals(63, ServerTypes::SKYBLOCK);
            $this->assertEquals(58, ServerTypes::BEDWARS);
            $this->assertEquals(51, ServerTypes::SKYWARS);
            $this->assertEquals(13, ServerTypes::WALLS3);
            $this->assertEquals(6, ServerTypes::TNTGAMES);
            $this->assertEquals(59, ServerTypes::MURDER_MYSTERY);
            $this->assertEquals(61, ServerTypes::DUELS);
            $this->assertEquals(20, ServerTypes::UHC);
            $this->assertEquals(21, ServerTypes::MCGO);
            $this->assertEquals(5, ServerTypes::SURVIVAL_GAMES);
        }

        public function testServerTypesFromEnum(): void {
            $skyblock = ServerTypes::fromEnum('SKYBLOCK');
            $this->assertNotNull($skyblock);
            $this->assertEquals('SkyBlock', $skyblock->getName());
            $this->assertEquals('SkyBlock', $skyblock->getDb());

            $bedwars = ServerTypes::fromEnum('BEDWARS');
            $this->assertNotNull($bedwars);
            $this->assertEquals('Bed Wars', $bedwars->getName());
        }

        public function testServerTypesFromId(): void {
            $skyblock = ServerTypes::fromID(63);
            $this->assertNotNull($skyblock);
            $this->assertEquals('SkyBlock', $skyblock->getName());
        }

        public function testApiBaseUrlIsV2(): void {
            $this->assertEquals('https://api.hypixel.net/v2/', Fetcher::BASE_URL, 
                'API should use v2 endpoints to avoid 404 errors');
        }

        public function testOldGameTypesClassDoesNotExist(): void {
            $this->assertFalse(
                class_exists('Plancke\\HypixelPHP\\classes\\gameType\\GameTypes', false),
                'Old GameTypes class should not exist in the new library version'
            );
        }
    }
