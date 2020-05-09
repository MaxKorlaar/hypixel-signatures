<?php
/**
 * Copyright (c) 2020 Max Korlaar
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

    namespace App\Utilities;

    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\fetch\FetchParams;
    use Plancke\HypixelPHP\fetch\Response;
    use Plancke\HypixelPHP\HypixelPHP;
    use Plancke\HypixelPHP\log\impl\NoLogger;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class HypixelAPI
     *
     * @package App\Utilities
     */
    class HypixelAPI {
        /**
         * @var HypixelPHP $api
         */
        protected $api;

        /**
         * HypixelAPI constructor.
         *
         * @throws HypixelPHPException
         */
        public function __construct() {
            $this->api = new HypixelPHP(config('signatures.api_key'));
            $this->api->setLogger(new NoLogger($this->api));
            $this->api->getCacheHandler()->setBaseDirectory(storage_path('app/cache/hypixelphp'));
            $this->api->getFetcher()->setTimeOut(config('signatures.api_timeout'));
        }

        /**
         * @param string $uuid
         *
         * @return HypixelObject|Response|Player|null
         * @throws HypixelPHPException
         */
        public function getPlayerByUuid(string $uuid) {
            return $this->api->getPlayer([FetchParams::PLAYER_BY_UUID => $uuid]);
        }

        /**
         * @return HypixelPHP
         */
        public function getApi(): HypixelPHP {
            return $this->api;
        }
    }