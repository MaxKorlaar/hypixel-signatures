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

    namespace App\Utilities\MinecraftAvatar;

    use Cache;
    use JsonException;
    use Log;
    use Psr\SimpleCache\InvalidArgumentException;

    /**
     * Class MojangAPI
     *
     * @author  Max Korlaar
     * @license MIT
     */
    class MojangAPI {
        private readonly string $sessionURL;
        private readonly string $profileURL;
        private $timeout;
        private $cacheTime;

        /**
         */
        public function __construct() {
            $this->sessionURL = 'https://sessionserver.mojang.com/session/minecraft/profile/';
            $this->profileURL = 'https://api.mojang.com/users/profiles/minecraft/';
            $this->timeout    = config('signatures.api_timeout');
            $this->cacheTime  = config('cache.times.mojang_api');
        }

        /**
         * @param $uuid
         *
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        public function getProfile($uuid): array {
            $cacheKey = 'mojangapi.uuid.' . $uuid;

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $request = $this->request($this->sessionURL . $uuid);

            if ($request['success'] === false) {
                if ($this->checkForThrottle($request)) {
                    Log::debug('Could not fetch profile from Mojang API due to throttle', ['uuid' => $uuid]);

                    return ['success' => false, 'throttle' => true];
                }

                Log::stack(['daily'])->warning('Something went wrong while fetching profile data', ['url' => $this->sessionURL . $uuid, 'return' => $request]);
                return $request;
            }

            $jsonArray    = json_decode((string) $request['data'], true, 512, JSON_THROW_ON_ERROR);
            $texturesJSON = $jsonArray['properties'][0];
            $textures     = json_decode(base64_decode((string) $texturesJSON['value']), true, 512, JSON_THROW_ON_ERROR);
            if (isset($textures['textures']['SKIN'])) {
                $skinArray = $textures['textures']['SKIN'];
                $isSteve = isset($skinArray['metadata']['model']) ? $skinArray['metadata']['model'] !== 'slim' : true;
                $skinURL = $skinArray['url'];
            } else { // https://github.com/crafatar/crafatar/blob/9d2fe0c45424de3ebc8e0b10f9446e7d5c3738b2/lib/skins.js#L90-L108
                $skinURL = null;

                $leastSignificantBits = (intval($uuid[7], 16) ^
                    intval($uuid[15], 16) ^
                    intval($uuid[23], 16) ^
                    intval($uuid[31], 16));

                $isSteve = !(bool) $leastSignificantBits;
            }
            $profileData = ['skinURL' => $skinURL, 'isSteve' => $isSteve, 'username' => $jsonArray['name'], 'uuid' => $jsonArray['id']];
            $return      = ['success' => true, 'data' => $profileData];

            Log::debug('Fetched profile from Mojang', ['uuid' => $uuid, 'data' => $profileData]);

            Cache::set($cacheKey, $return, $this->getCacheTime());

            return $return;
        }

        /**
         * @param $url
         */
        private function request($url): array {
            /*
             * Set cURL properties
             */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->timeout);
            $curlOut = curl_exec($ch);

            if ($curlOut === false) {
                Log::stack(['daily'])->warning('Something went wrong while fetching from the Mojang API', ['error' => curl_error($ch), 'url' => $url]);
                return ['success' => false, 'status_code' => null, 'error' => curl_error($ch)];
            }

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status !== 200) {
                return ['success' => false, 'status_code' => $status, 'error' => null];
            }

            return ['success' => true, 'data' => $curlOut];
        }

        /**
         * @param $request
         */
        public function checkForThrottle($request): bool {
            return isset($request['status_code']) && $request['status_code'] === 429;
        }

        /**
         * @return mixed
         */
        public function getCacheTime() {
            return $this->cacheTime;
        }

        /**
         * @param mixed $cacheTime
         */
        public function setCacheTime($cacheTime): void {
            $this->cacheTime = $cacheTime;
        }

        /**
         * @param $username
         *
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        public function getUUID($username): array {
            $cacheKey = 'mojangapi.username.' . $username;

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $request = $this->request($this->profileURL . $username);

            if ($request['success'] === false) {
                if ($this->checkForThrottle($request)) {
                    Log::debug('Could not fetch UUID from Mojang API due to throttle', ['username' => $username]);

                    return ['success' => false, 'throttle' => true, 'status_code' => null, 'error' => 'throttle'];
                }
                return $request;
            }

            $jsonArray = json_decode((string) $request['data'], true, 512, JSON_THROW_ON_ERROR);
            $return    = ['success' => true, 'data' => $jsonArray];

            Log::debug('Fetched UUID from Mojang API', ['username' => $username, 'data' => $jsonArray]);

            Cache::set($cacheKey, $return, $this->getCacheTime());

            return $return;
        }

        public function getTimeout(): int {
            return $this->timeout;
        }

        /**
         * @param int $timeout
         */
        public function setTimeout($timeout): void {
            $this->timeout = $timeout;
        }

    }
