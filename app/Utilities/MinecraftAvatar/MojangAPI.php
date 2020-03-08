<?php

    namespace App\Utilities\MinecraftAvatar;

    use Cache;

    /**
     * Class MojangAPI
     *
     * @author  Max Korlaar
     * @license MIT
     */
    class MojangAPI {
        private $sessionURL;
        private $profileURL;
        private $timeout;
        private $cacheTime;

        /**
         */
        public function __construct() {
            $this->sessionURL = 'https://sessionserver.mojang.com/session/minecraft/profile/';
            $this->profileURL = 'https://api.mojang.com/users/profiles/minecraft/';
            $this->timeout    = 3;
            $this->cacheTime  = 2 * 7200;
        }

        /**
         * @param $uuid
         *
         * @return array
         */
        public function getProfile($uuid): array {
            return Cache::remember('mojangapi.uuid.' . $uuid, $this->getCacheTime(), function () use ($uuid) {
                $request = $this->request($this->sessionURL . $uuid);
                if ($request['success'] === false) {
                    return $request;
                }
                $jsonArray    = json_decode($request['data'], true);
                $texturesJSON = $jsonArray['properties'][0];
                $textures     = json_decode(base64_decode($texturesJSON['value']), true);
                if (isset($textures['textures']['SKIN'])) {
                    $skinArray = $textures['textures']['SKIN'];
                    if (isset($skinArray['metadata']['model'])) {
                        $isSteve = $skinArray['metadata']['model'] !== 'slim';
                    } else {
                        $isSteve = true;
                    }
                    $skinURL = $skinArray['url'];
                } else { // https://github.com/mapcrafter/mapcrafter-playermarkers/blob/master/playermarkers/player.php#L8-L19
                    $skinURL = null;
                    for ($i = 0; $i < 4; $i++) {
                        $sub[$i] = intval('0x' . substr($uuid, $i * 8, 8) + 0, 16);
                    }
                    if ((bool)((($sub[0] ^ $sub[1]) ^ ($sub[2] ^ $sub[3])) % 2) === true) {
                        $isSteve = false;
                    } else {
                        $isSteve = true;
                    }
                }
                $profileData = ['skinURL' => $skinURL, 'isSteve' => $isSteve, 'username' => $jsonArray['name'], 'uuid' => $jsonArray['id']];
                return ['success' => true, 'data' => $profileData];
            });
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
         * @param $url
         *
         * @return array
         */
        private function request($url): array {

            /*
             * Set cURL properties
             */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            $curlOut = curl_exec($ch);

            if ($curlOut === false) {
                return ['success' => false, 'statusCode' => null, 'error' => curl_error($ch)];
            }
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status !== 200) {
                return ['success' => false, 'statusCode' => $status, 'error' => null];
            }
            return ['success' => true, 'data' => $curlOut];
        }

        /**
         * @param $username
         *
         * @return array
         */
        public function getUUID($username): array {
            return Cache::remember('mojangapi.username.' . $username, $this->getCacheTime(), function () use ($username) {
                $request = $this->request($this->profileURL . $username);
                if ($request['success'] === false) {
                    if ($this->checkForThrottle($request)) {
                        return ['success' => false, 'throttle' => true];
                    }

                    return $request;
                }
                $jsonArray = json_decode($request['data'], true);
                return ['success' => true, 'data' => $jsonArray];
            });

        }

        /**
         * @param $request
         *
         * @return bool
         */
        public function checkForThrottle($request): bool {
            return isset($request['statusCode']) && $request['statusCode'] === 429;
        }

        /**
         * @return int
         */
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
