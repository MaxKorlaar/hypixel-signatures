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
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\guild\Guild;
    use Tests\TestCase;

    /**
     * Integration tests for Guild API endpoints to verify the custom cache handler fix
     * 
     * These tests confirm that:
     * 1. Guild lookups by player UUID work correctly
     * 2. Guild lookups by name work correctly
     * 3. Guild lookups by ID work correctly
     * 4. No "not implemented" exceptions are thrown
     *
     * @package Tests\Feature
     */
    class GuildApiIntegrationTest extends TestCase {
        /**
         * Test that guild can be fetched by player UUID without throwing exceptions
         * 
         * @throws HypixelPHPException
         */
        public function testCanFetchGuildByPlayerUuid(): void {
            $api = new HypixelAPI();
            
            // Use Technoblade's UUID - a well-known player who was in guilds
            $uuid = 'b876ec32e396476ba1158438d83c67d4';
            
            // This should not throw an InvalidArgumentException about "not implemented"
            $guild = $api->getGuildByPlayerUuid($uuid);
            
            // Guild might be null if the player is not in a guild
            // But the call should not throw an exception
            $this->assertTrue(
                $guild === null || $guild instanceof Guild,
                'getGuildByPlayerUuid should return null or Guild object, not throw an exception'
            );
        }

        /**
         * Test that guild can be fetched by name without throwing exceptions
         * 
         * @throws HypixelPHPException
         */
        public function testCanFetchGuildByName(): void {
            $api = new HypixelAPI();
            
            // Use a well-known guild name (The Sloths is a prominent guild)
            $guildName = 'The Sloths';
            
            // This should not throw an InvalidArgumentException about "not implemented"
            $guild = $api->getGuildByName($guildName);
            
            // Guild might be null if the guild doesn't exist
            // But the call should not throw an exception about not being implemented
            $this->assertTrue(
                $guild === null || $guild instanceof Guild,
                'getGuildByName should return null or Guild object, not throw "not implemented" exception'
            );
            
            // If the guild was found, verify it has expected properties
            if ($guild instanceof Guild) {
                $this->assertNotEmpty($guild->getID(), 'Guild should have an ID');
                $this->assertNotEmpty($guild->getName(), 'Guild should have a name');
            }
        }

        /**
         * Test that guild can be fetched by ID without throwing exceptions
         * 
         * @throws HypixelPHPException
         */
        public function testCanFetchGuildById(): void {
            $api = new HypixelAPI();
            
            // First, try to get a guild by a known player to get a valid guild ID
            $uuid = 'b876ec32e396476ba1158438d83c67d4';
            $guild = $api->getGuildByPlayerUuid($uuid);
            
            if ($guild instanceof Guild) {
                $guildId = $guild->getID();
                
                // Now fetch by ID
                $guildById = $api->getGuildById($guildId);
                
                $this->assertInstanceOf(Guild::class, $guildById, 'Should return a Guild object');
                $this->assertEquals($guildId, $guildById->getID(), 'Guild IDs should match');
            } else {
                $this->markTestSkipped('Could not fetch guild for testing - player may not be in a guild');
            }
        }

        /**
         * Test that HypixelAPI.isValidMongoId works correctly
         */
        public function testIsValidMongoId(): void {
            // Valid Mongo ID (24 hex characters)
            $validId = '507f1f77bcf86cd799439011';
            $this->assertTrue(
                HypixelAPI::isValidMongoId($validId),
                'Should recognize valid MongoDB ObjectId'
            );
            
            // Invalid IDs
            $this->assertFalse(
                HypixelAPI::isValidMongoId('too-short'),
                'Should reject IDs that are too short'
            );
            
            $this->assertFalse(
                HypixelAPI::isValidMongoId('507f1f77bcf86cd79943901g'), // Contains 'g'
                'Should reject IDs with non-hex characters'
            );
            
            $this->assertFalse(
                HypixelAPI::isValidMongoId('507f1f77bcf86cd7994390111'), // 25 characters
                'Should reject IDs that are too long'
            );
        }

        /**
         * Test that the cache handler methods don't throw "not implemented" exceptions
         * This is the core fix being tested - ensuring methods exist and work
         */
        public function testCacheHandlerMethodsDoNotThrowNotImplementedException(): void {
            $api = new HypixelAPI();
            $cacheHandler = $api->getApi()->getCacheHandler();
            
            // These should not throw InvalidArgumentException with "not implemented" message
            try {
                $result1 = $cacheHandler->getGuildByPlayer('test-uuid');
                $this->assertNull($result1, 'Should return null for non-existent cached guild');
                
                $result2 = $cacheHandler->getGuildByName('test-guild-name');
                $this->assertNull($result2, 'Should return null for non-existent cached guild');
                
                // Test passes - no exception thrown
                $this->assertTrue(true, 'Cache handler methods work without throwing exceptions');
            } catch (\InvalidArgumentException $e) {
                if (str_contains($e->getMessage(), 'not implemented')) {
                    $this->fail('Cache handler methods should not throw "not implemented" exception: ' . $e->getMessage());
                }
                throw $e;
            }
        }
    }
