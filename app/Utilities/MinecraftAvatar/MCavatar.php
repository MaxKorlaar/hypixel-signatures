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

    namespace App\Utilities\MinecraftAvatar;

    use Cache;
    use JsonException;
    use Log;
    use Psr\SimpleCache\InvalidArgumentException;
    use RuntimeException;

    /**
     * Class MCavatar
     * Modified source code made into class
     *
     * @author      Max Korlaar
     * @description Class to make it easier to get Minecraft skins.
     * @license     MIT
     *              Some code was borrowed from an old opensource github project, which was not
     *              working very well.
     *              TODO Find old URL
     */
    class MCavatar {
        public const STEVE_SKIN = 'https://hypixel.maxkorlaar.com/img/Steve_skin.png';
        public $name;
        public $skinUrl;
        public $size;
        public $imageStoragePath;
        public $helm = true;
        public $fetchError = null;
        public int $imageQuality = 80;

        protected $fallbackUrl;
        protected $fallbackSkinRegular;
        protected $fallbackSkinThin;

        /**
         * Defines url
         */
        public function __construct() {
            $this->skinUrl          = 'http://skins.minecraft.net/MinecraftSkins/';
            $this->imageStoragePath = storage_path('app/public/minecraft-avatars') . '/';

            $this->fallbackSkinRegular = resource_path('images/skins/steve.png');
            $this->fallbackSkinThin    = resource_path('images/skins/alex.png');
            $this->fallbackUrl         = $this->fallbackSkinRegular;
        }

        /**
         * @param      $username
         *
         * @return string Path to skin image
         */
        public function getSkinFromCache($username): string {
            $imagepath = $this->imageStoragePath . 'full_skin/' . strtolower($username) . '.webp';

            return Cache::lock('minecraft.avatar.' . $imagepath)->block(5, function () use ($imagepath, $username) {
                if (file_exists($imagepath)) {
                    if (filemtime($imagepath) < strtotime('-2 week')) {
                        Log::debug('Full skin expired, redownloading', ['username' => $username]);

                        return $this->getSkin($username, true);
                    }

                    return $imagepath;
                }

                Log::debug('Full skin not yet downloaded, downloading', ['username' => $username]);

                return $this->getSkin($username, true);
            });
        }

        /**
         * @param      $username
         * @param bool $save
         *
         * @return resource|string
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        public function getSkin($username, $save = false) {
            $this->fallbackUrl = $this->fallbackSkinRegular;

            if (strlen($username) >= 32) {
                if (strlen($username) === 32) {
                    $api  = new MojangAPI();
                    $data = $api->getProfile($username);
                } else {
                    $data = [
                        'success' => true,
                        'data'    => [
                            'skinURL' => 'http://textures.minecraft.net/texture/' . $username,
                            'isSteve' => true
                        ]
                    ];
                }

                if ($data['success'] === true) {
                    $skinData = $data['data'];

                    $this->fallbackUrl = $skinData['isSteve'] ? $this->fallbackSkinRegular : $this->fallbackSkinThin;

                    if ($skinData['skinURL'] === null) {
                        $skinURL = $this->fallbackUrl;
                        Log::debug('Player has not set a skin', [$skinData, 'url' => $skinURL]);
                    } else {
                        $skinURL = $skinData['skinURL'];
                    }

                    $src = imagecreatefrompng($skinURL);

                    if (!$src) {
                        Log::debug('Could not create skin image from url, falling back on default', ['url' => $skinURL]);
                        $this->fetchError = true;

                        return $this->fallbackUrl;
                    }
                    Log::debug('Downloaded from ' . $skinURL);
                } else {
                    Log::warning('Falling back on steve skin, could not fetch player profile from Mojang', ['data' => $data]);
                    $this->fetchError = true;

                    return $this->fallbackUrl;
                }
            } else {
                $api  = new MojangAPI();
                $uuid = $api->getUUID($username);
                if ($uuid['success']) {
                    return $this->getSkin($uuid['data']['id'], $save);
                }

                Log::warning('Falling back on steve skin, could not fetch player UUID from Mojang.', ['username' => $username, 'data' => $uuid]);
                $this->fetchError = true;

                return $this->fallbackUrl;
            }

            imageAlphaBlending($src, true);
            imageSaveAlpha($src, true);

            if ($save) {
                $imagepath = $this->imageStoragePath . 'full_skin/' . strtolower($username) . '.webp';

                if (!file_exists($this->imageStoragePath . 'full_skin/') && !mkdir($concurrentDirectory = $this->imageStoragePath . 'full_skin/', 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }

                imagepalettetotruecolor($src);
                imagewebp($src, $imagepath, $this->imageQuality);

                return $imagepath;
            }

            return $src;
        }

        /**
         * @param      $username
         * @param int  $size
         * @param bool $helm
         *
         * @usage getFromCache('MegaMaxsterful');
         * @return string
         */
        public function getFromCache($username, $size = 100, $helm = true): string {
            if ($helm) {
                $imagepath = $this->imageStoragePath . $size . 'px/' . strtolower($username) . '.webp';
            } else {
                $imagepath = $this->imageStoragePath . $size . 'px-no-helm/' . strtolower($username) . '.webp';
            }
            $this->name = $username;
            $this->size = $size;
            $this->helm = $helm;

            if (file_exists($imagepath)) {
                if (filemtime($imagepath) < strtotime('-2 week')) {
                    return $this->getImage($username, $size, $helm);
                }

                return $imagepath;
            }

            return $this->getImage($username, $size, $helm);
        }

        /**
         * @param      $username
         * @param int  $size
         * @param bool $helm
         * @param bool $save
         *
         * @return string
         */
        public function getImage($username, $size = 100, $helm = true, $save = true): string {
            $this->name = $username;
            $this->size = $size;

            $skinPath = $this->getSkinFromCache($username);
            $src      = imagecreatefromwebp($skinPath);

            $dest = imagecreatetruecolor(8, 8);
            imagecopy($dest, $src, 0, 0, 8, 8, 8, 8);
            if ($helm) {
                $bg_color = imagecolorat($src, 0, 0);
                $no_helm  = true;
                for ($i = 1; $i <= 8; $i++) {
                    for ($j = 1; $j <= 4; $j++) {
                        if (imagecolorat($src, 39 + $i, 7 + $j) !== $bg_color) {
                            $no_helm = false;
                        }
                    }

                    if (!$no_helm) {
                        break;
                    }
                }
                if (!$no_helm) {
                    imagecopy($dest, $src, 0, 0, 40, 8, 8, 8);
                }
            }
            $final = imagecreatetruecolor($size, $size);
            imagecopyresized($final, $dest, 0, 0, 0, 0, $size, $size, 8, 8);

            if ($helm) {
                if (!file_exists($this->imageStoragePath . $size . 'px/') && !mkdir($concurrentDirectory = $this->imageStoragePath . $size . 'px/', 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                $imagepath = $this->imageStoragePath . $size . 'px/' . strtolower($username) . '.webp';
            } else {
                if (!file_exists($this->imageStoragePath . $size . 'px-no-helm/') && !mkdir($concurrentDirectory = $this->imageStoragePath . $size . 'px-no-helm/', 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                $imagepath = $this->imageStoragePath . $size . 'px-no-helm/' . strtolower($username) . '.webp';
            }

            if ($save) {
                imagewebp($final, $imagepath, $this->imageQuality);
            }

            return $imagepath;
        }

        /**
         * @return mixed
         */
        public function getName() {
            return $this->name;
        }

        /**
         * @param mixed $name
         */
        public function setName($name): void {
            $this->name = $name;
        }

        /**
         * @return string
         */
        public function getFallbackUrl(): string {
            return $this->fallbackUrl;
        }
    }

