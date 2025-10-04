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

    use App\Utilities\CustomFlatFileCacheHandler;
    use App\Utilities\HypixelAPI;
    use Plancke\HypixelPHP\cache\impl\FlatFileCacheHandler;
    use Tests\TestCase;

    /**
     * Test that the CustomFlatFileCacheHandler properly implements guild lookup methods
     * that were throwing exceptions in the base FlatFileCacheHandler
     *
     * @package Tests\Unit
     */
    class CustomCacheHandlerTest extends TestCase {
        /**
         * Test that CustomFlatFileCacheHandler extends FlatFileCacheHandler
         */
        public function testCustomCacheHandlerExtendsFlatFileCacheHandler(): void {
            $api = new HypixelAPI();
            $cacheHandler = $api->getApi()->getCacheHandler();
            
            $this->assertInstanceOf(FlatFileCacheHandler::class, $cacheHandler,
                'Cache handler should be an instance of FlatFileCacheHandler');
            $this->assertInstanceOf(CustomFlatFileCacheHandler::class, $cacheHandler,
                'Cache handler should be our custom implementation');
        }

        /**
         * Test that getGuildByPlayer method exists and doesn't throw exceptions
         */
        public function testGetGuildByPlayerMethodExists(): void {
            $api = new HypixelAPI();
            $cacheHandler = $api->getApi()->getCacheHandler();
            
            $this->assertTrue(method_exists($cacheHandler, 'getGuildByPlayer'),
                'getGuildByPlayer method should exist');
            
            // Test that it doesn't throw an exception when called with a UUID
            // It should return null if no cached guild is found
            $result = $cacheHandler->getGuildByPlayer('test-uuid-12345678');
            $this->assertNull($result, 'Should return null when guild is not in cache');
        }

        /**
         * Test that getGuildByName method exists and doesn't throw exceptions
         */
        public function testGetGuildByNameMethodExists(): void {
            $api = new HypixelAPI();
            $cacheHandler = $api->getApi()->getCacheHandler();
            
            $this->assertTrue(method_exists($cacheHandler, 'getGuildByName'),
                'getGuildByName method should exist');
            
            // Test that it doesn't throw an exception when called with a name
            // It should return null if no cached guild is found
            $result = $cacheHandler->getGuildByName('test-guild-name');
            $this->assertNull($result, 'Should return null when guild is not in cache');
        }

        /**
         * Test that HypixelAPI uses the custom cache handler
         */
        public function testHypixelApiUsesCustomCacheHandler(): void {
            $api = new HypixelAPI();
            $cacheHandler = $api->getApi()->getCacheHandler();
            
            $this->assertInstanceOf(CustomFlatFileCacheHandler::class, $cacheHandler,
                'HypixelAPI should use CustomFlatFileCacheHandler');
        }

        /**
         * Test that the cache handler base directory is set correctly
         */
        public function testCacheHandlerBaseDirectoryIsSet(): void {
            $api = new HypixelAPI();
            $cacheHandler = $api->getApi()->getCacheHandler();
            
            $baseDirectory = $cacheHandler->getBaseDirectory();
            $this->assertNotEmpty($baseDirectory, 'Base directory should be set');
            $this->assertStringContainsString('storage', $baseDirectory,
                'Base directory should be in the storage path');
        }
    }
