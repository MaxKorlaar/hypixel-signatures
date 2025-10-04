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

    use App\Utilities\HypixelAPI;
    use Plancke\HypixelPHP\classes\serverType\ServerTypes;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\player\Player;
    use Tests\TestCase;

    /**
     * Integration tests for Hypixel API to verify the v2 endpoint upgrade
     * 
     * These tests confirm that:
     * 1. The API key is configured correctly
     * 2. The v2 endpoints are being used (no 404 errors)
     * 3. Player data can be fetched successfully
     * 4. SkyBlock profiles can be fetched successfully
     * 5. ServerTypes migration is working correctly
     *
     * @package Tests\Feature
     */
    class HypixelApiIntegrationTest extends TestCase {
        /**
         * Test that API key is configured
         */
        public function testApiKeyIsConfigured(): void {
            $apiKey = config('signatures.api_key');
            $this->assertNotEmpty($apiKey, 'HYPIXEL_API_KEY should be configured');
            $this->assertIsString($apiKey);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $apiKey, 'API key should be a valid UUID format');
        }

        /**
         * Test that HypixelAPI can be instantiated
         * 
         * @throws HypixelPHPException
         */
        public function testHypixelApiCanBeInstantiated(): void {
            $api = new HypixelAPI();
            $this->assertInstanceOf(HypixelAPI::class, $api);
        }

        /**
         * Test fetching a well-known player (Notch/069a79f4-44e9-4726-a5be-fca90e38aaf5)
         * This verifies the v2 player endpoint works
         * 
         * @throws HypixelPHPException
         */
        public function testCanFetchPlayerData(): void {
            $api = new HypixelAPI();
            
            // Notch's UUID - a well-known Minecraft player
            $uuid = '069a79f444e94726a5befca90e38aaf5';
            
            $player = $api->getPlayerByUuid($uuid);
            
            $this->assertInstanceOf(Player::class, $player, 'Should return a Player object');
            $this->assertEquals($uuid, $player->getUUID(), 'UUID should match');
            $this->assertNotEmpty($player->getName(), 'Player should have a name');
        }

        /**
         * Test that ServerTypes constants work correctly
         * This verifies the GameTypes → ServerTypes migration
         * 
         * @throws HypixelPHPException
         */
        public function testServerTypesWorksWithPlayerStats(): void {
            $api = new HypixelAPI();
            
            // Use Notch's UUID
            $uuid = '069a79f444e94726a5befca90e38aaf5';
            
            $player = $api->getPlayerByUuid($uuid);
            $this->assertInstanceOf(Player::class, $player);
            
            $stats = $player->getStats();
            $this->assertNotNull($stats, 'Player should have stats');
            
            // Test that we can access game stats using ServerTypes constants
            // This verifies the migration from GameTypes to ServerTypes works
            $skyblockStats = $stats->getGameFromID(ServerTypes::SKYBLOCK);
            $this->assertNotNull($skyblockStats, 'Should be able to get SkyBlock stats using ServerTypes');
            
            $bedwarsStats = $stats->getGameFromID(ServerTypes::BEDWARS);
            $this->assertNotNull($bedwarsStats, 'Should be able to get BedWars stats using ServerTypes');
        }

        /**
         * Test fetching SkyBlock profiles
         * This is the critical test for the 404 error fix mentioned in the issue
         * 
         * @throws HypixelPHPException
         */
        public function testCanFetchSkyBlockProfiles(): void {
            $api = new HypixelAPI();
            
            // Use Technoblade's UUID - a well-known player who plays SkyBlock
            $uuid = 'b876ec32e396476ba1158438d83c67d4';
            
            $player = $api->getPlayerByUuid($uuid);
            $this->assertInstanceOf(Player::class, $player);
            
            $stats = $player->getStats();
            $skyblockStats = $stats->getGameFromID(ServerTypes::SKYBLOCK);
            
            // Check if the player has SkyBlock profiles
            $profiles = $skyblockStats->get('profiles', []);
            
            // The test passes as long as we can fetch the data without 404 errors
            $this->assertIsArray($profiles, 'SkyBlock profiles should be an array');
            $this->assertNotEmpty($profiles, 'Technoblade should have SkyBlock profiles');
            
            // Verify we can access profile IDs
            $firstProfile = reset($profiles);
            $this->assertArrayHasKey('profile_id', $firstProfile, 'Profile should have a profile_id');
            
            // Try to fetch the actual profile data using the v2 endpoint
            $profileId = $firstProfile['profile_id'];
            $skyBlockProfile = $player->getHypixelPHP()->getSkyBlockProfile($profileId);
            
            // The key test: This should not return a 404 error
            // If we get here without an exception, the v2 endpoint is working
            $this->assertNotNull($skyBlockProfile, 'Should be able to fetch SkyBlock profile without 404 error');
            
            // Verify the profile has data
            $this->assertNotEmpty($skyBlockProfile->getData(), 'SkyBlock profile should have data');
            
            // Verify we can get members from the profile
            $members = $skyBlockProfile->getMembers();
            $this->assertIsArray($members, 'Profile should have members array');
            $this->assertNotEmpty($members, 'Profile should have at least one member');
        }

        /**
         * Test that the API doesn't throw 404 errors
         * This is a smoke test to ensure the v2 endpoints are being used
         * 
         * @throws HypixelPHPException
         */
        public function testNoFourOhFourErrors(): void {
            $api = new HypixelAPI();
            
            try {
                $uuid = '069a79f444e94726a5befca90e38aaf5';
                $player = $api->getPlayerByUuid($uuid);
                
                $this->assertInstanceOf(Player::class, $player);
                
                // If we get here, no 404 errors were thrown
                $this->assertTrue(true, 'API calls completed without 404 errors');
            } catch (\Plancke\HypixelPHP\exceptions\BadResponseCodeException $e) {
                // If we get a 404, the test should fail with a clear message
                if ($e->getActualCode() === 404) {
                    $this->fail('Got 404 error from Hypixel API - v2 endpoints may not be configured correctly: ' . $e->getMessage());
                }
                throw $e;
            }
        }

        /**
         * Test ServerTypes enum methods work correctly
         */
        public function testServerTypesEnumMethodsWork(): void {
            // Test fromEnum method
            $skyblock = ServerTypes::fromEnum('SKYBLOCK');
            $this->assertNotNull($skyblock, 'fromEnum should work for SKYBLOCK');
            $this->assertEquals('SkyBlock', $skyblock->getName());
            
            $bedwars = ServerTypes::fromEnum('BEDWARS');
            $this->assertNotNull($bedwars, 'fromEnum should work for BEDWARS');
            $this->assertEquals('Bed Wars', $bedwars->getName());
            
            // Test that old GameTypes class doesn't exist
            $this->assertFalse(
                class_exists('Plancke\\HypixelPHP\\classes\\gameType\\GameTypes', false),
                'Old GameTypes class should not exist'
            );
        }
    }
