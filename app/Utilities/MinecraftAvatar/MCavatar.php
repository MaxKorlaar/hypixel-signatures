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
        public $skinurl;
        public $fetchUrl;
        public $size;
        public $imagepath;
        public $cacheInfo;
        public $helm = true;
        public $fetchError = null;

        /**
         * Defines url
         */
        public function __construct() {
            $this->skinurl   = 'http://skins.minecraft.net/MinecraftSkins/';
            $this->imagepath = storage_path('app/public/minecraft-avatars') . '/';
        }

        /**
         * @param      $username
         * @param bool $save
         *
         * @return resource|string
         */
        public function getSkinFromCache($username, $save = true) {
            $imagepath = $this->imagepath . 'full_skin/' . strtolower($username) . '.png';

            return Cache::lock('minecraft.avatar.' . $imagepath)->block(5, function () use ($imagepath, $username, $save) {
                if (file_exists($imagepath)) {
                    if (filemtime($imagepath) < strtotime('-2 week')) {
                        Log::debug('Full skin expired, redownloading', ['username' => $username]);

                        return $this->getSkin($username, $save);
                    }

                    return $imagepath;
                }

                Log::debug('Full skin not yet downloaded, downloading', ['username' => $username]);

                return $this->getSkin($username, $save);
            });
        }

        /**
         * @param      $username
         * @param bool $save
         *
         * @return resource|string
         * @throws InvalidArgumentException
         */
        public function getSkin($username, $save = false) {
            if (strlen($username) === 32) {
                $api  = new MojangAPI();
                $data = $api->getProfile($username);
                if ($data['success'] === true) {
                    $skinData = $data['data'];
                    if ($skinData['skinURL'] === null) {
                        $imgURL          = $skinData['isSteve'] ? self::STEVE_SKIN : 'https://minecraft.net/images/alex.png';
                        $this->cacheInfo = 'image not yet downloaded - default';
                        Log::debug('image not yet downloaded - default');
                    } else {
                        $imgURL = $skinData['skinURL'];

                    }
                    $this->fetchUrl = $imgURL;
                    $src            = imagecreatefrompng($imgURL);
                    if (!$src) {
                        Log::debug('Source is false', [$this->fetchUrl]);
                        $src              = imagecreatefrompng(self::STEVE_SKIN);
                        $this->fetchError = true;
                        $save             = false;
                    }
                    $this->cacheInfo = 'Downloaded from ' . $imgURL;
                    Log::debug('Downloaded from ' . $imgURL);
                } else {
                    $src             = imagecreatefrompng(self::STEVE_SKIN);
                    $this->cacheInfo = 'image not yet downloaded - unknown error while getting player profile';
                    Log::warning('image not yet downloaded - unknown error while getting player profile', [$data]);
                    $this->fetchError = true;
                    $save             = false;
                }
            } else {
                //$src            = @imagecreatefrompng("http://skins.minecraft.net/MinecraftSkins/{$username}.png");
                //$this->fetchUrl = "http://skins.minecraft.net/MinecraftSkins/{$username}.png";
                $api  = new MojangAPI();
                $uuid = $api->getUUID($username);
                if ($uuid['success']) {
                    return $this->getSkin($uuid['data']['id'], $save);
                }

                $src             = imagecreatefrompng(self::STEVE_SKIN);
                $this->cacheInfo = 'image not yet downloaded - unknown error while fetching skin from username. Last resort: ' . self::STEVE_SKIN;
                Log::warning('image not yet downloaded - unknown error while fetching skin from username. Last resort: ' . self::STEVE_SKIN);
                $this->fetchError = true;
                $save             = false;
            }

            imageAlphaBlending($src, true);
            imageSaveAlpha($src, true);
            if ($save) {
                $imagepath = $this->imagepath . 'full_skin/' . strtolower($username) . '.png';
                if (!file_exists($this->imagepath . 'full_skin/') && !mkdir($concurrentDirectory = $this->imagepath . 'full_skin/', 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                imagepng($src, $imagepath);
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
                $imagepath = $this->imagepath . $size . 'px/' . strtolower($username) . '.png';
            } else {
                $imagepath = $this->imagepath . $size . 'px-no-helm/' . strtolower($username) . '.png';
            }
            $this->name = $username;
            $this->size = $size;
            $this->helm = $helm;

            if (file_exists($imagepath)) {
                if (filemtime($imagepath) < strtotime('-2 week')) {
                    $this->cacheInfo = 'expired, redownloading';
                    unlink($imagepath);
                    return $this->getImage($username, $size, $helm);
                }

                $this->cacheInfo = 'not expired';
                return $imagepath;
            }

            $this->cacheInfo = 'image not yet downloaded';
            return $this->getImage($username, $size, $helm);
        }

        /**
         * @param      $username
         * @param int  $size
         * @param bool $helm
         * @param bool $save
         *
         * @return string
         * @throws InvalidArgumentException
         */
        public function getImage($username, $size = 100, $helm = true, $save = true): string {
            $this->name  = $username;
            $this->size  = $size;
            $defaultSkin = null;

            if (strlen($username) === 32) {
                $api  = new MojangAPI();
                $data = $api->getProfile($username);
                if ($data['success'] === true) {
                    $skinData = $data['data'];
                    if ($skinData['skinURL'] === null) {
                        $imgURL          = $skinData['isSteve'] ? self::STEVE_SKIN : 'https://minecraft.net/images/alex.png';
                        $this->cacheInfo = 'image not yet downloaded - default';
                    } else {
                        $imgURL = $skinData['skinURL'];

                    }
                    $this->fetchUrl = $imgURL;
                    $src            = imagecreatefrompng($imgURL);
                    if (!$src) {
                        $src = imagecreatefrompng(self::STEVE_SKIN);
                        Log::warning('image not yet downloaded - unknown error while downloading', ['username' => $username]);
                        $defaultSkin      = 'steve';
                        $this->fetchError = true;
                        $save             = false;
                    }
                } else {
                    $src = imagecreatefrompng(self::STEVE_SKIN);
                    Log::warning('image not yet downloaded - unknown error while getting player profile', ['username' => $username]);
                    $defaultSkin      = 'steve';
                    $this->fetchError = true;
                    $save             = false;
                }
            } else {
                $src            = imagecreatefrompng("http://skins.minecraft.net/MinecraftSkins/{$username}.png");
                $this->fetchUrl = "http://skins.minecraft.net/MinecraftSkins/{$username}.png";
                if (!$src) {
                    $src              = imagecreatefrompng(self::STEVE_SKIN);
                    $this->cacheInfo  = 'image not yet downloaded - unknown error while fetching skin from username';
                    $defaultSkin      = 'steve';
                    $this->fetchError = true;
                    $save             = false;
                }
            }

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
                if (!file_exists($this->imagepath . $size . 'px/') && !mkdir($concurrentDirectory = $this->imagepath . $size . 'px/', 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                $imagepath = $this->imagepath . $size . 'px/' . strtolower($username) . '.png';
            } else {
                if (!file_exists($this->imagepath . $size . 'px-no-helm/') && !mkdir($concurrentDirectory = $this->imagepath . $size . 'px-no-helm/', 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                $imagepath = $this->imagepath . $size . 'px-no-helm/' . strtolower($username) . '.png';
            }

            if ($save) {
                imagepng($final, $imagepath);
            }
            if ($defaultSkin !== null) {
                $imagepath = $this->imagepath . $size . 'px/' . $defaultSkin . '.png';
                imagepng($final, $imagepath);
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
    }

