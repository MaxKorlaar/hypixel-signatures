<?php
/*
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

    namespace App\Http\Controllers\Signatures;

    use App\Http\Controllers\Controller;
    use App\Utilities\HypixelAPI;
    use App\Utilities\MinecraftAvatar\ThreeDAvatar;
    use GDText\Box;
    use GDText\Color;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Log;
    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\exceptions\BadResponseCodeException;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\exceptions\InvalidUUIDException;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class BaseSignature
     *
     * @package App\Http\Controllers\Signatures
     */
    abstract class BaseSignature extends Controller {

        private const FULLY_TRANSPARENT = 127;

        /**
         * @var HypixelAPI $api
         */
        protected HypixelAPI $api;

        /**
         * BaseSignature constructor.
         *
         */
        public function __construct() {
            $this->api = new HypixelAPI();
        }

        /**
         * @param Request $request
         * @param string  $uuid
         *
         * @return Response
         */
        public function render(Request $request, string $uuid): Response {

            $playerOrResponse = $this->getPlayerData($uuid);
            if ($playerOrResponse instanceof Player) {
                return $this->signature($request, $playerOrResponse);
            }

            return $playerOrResponse;
        }

        /**
         * @param Player $player
         * @param        $image
         *
         * @return int[]
         * @todo Add functionality
         */
        protected function get2dAvatar(Player $player, &$image): array {
            $avatarWidth        = 0;
            $textX              = $avatarWidth + 5;
            $textBeneathAvatarX = $textX;

            return [$avatarWidth, $textX, $textBeneathAvatarX];
        }

        /**
         * @param Player $player
         * @param        $image
         *
         * @return int[]
         */
        protected function get3dAvatar(Player $player, &$image): array {
            $threedAvatar = new ThreeDAvatar();
            $avatarImage  = $threedAvatar->getThreeDSkinFromCache($player->getUUID(), 4, 30, false, true, true);

            $avatarWidth        = imagesx($avatarImage);
            $textX              = $avatarWidth + 5;
            $textBeneathAvatarX = $textX;

            imagecopy($image, $avatarImage, 0, 0, 0, 0, imagesx($avatarImage), imagesy($avatarImage));
            imagedestroy($avatarImage);

            return [$avatarWidth, $textX, $textBeneathAvatarX];
        }

        /**
         * @param string $uuid
         *
         * @return Response|Player
         */
        protected function getPlayerData(string $uuid) {
            try {
                $player = $this->api->getPlayerByUuid($uuid);

                /** @var HypixelObject $player */
                if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                    return self::generateErrorImage("Bad API response.\n{$player->getResponse()->getData()['cause']}");
                }

                if ($player instanceof Player) {
                    if (empty($player->getData())) {
                        return self::generateErrorImage('Player has not played on Hypixel before!', 404);
                    }
                    return $player;
                }

                Log::debug('Unexpected API response', ['uuid' => $uuid, 'response' => $player, 'api' => $this->api]);

                return self::generateErrorImage('Unexpected API response.');
            } catch (InvalidUUIDException $exception) {
                return self::generateErrorImage('UUID is invalid.', 400);
            } catch (BadResponseCodeException $exception) {
                if ($exception->getActualCode() === 429) {
                    return self::generateErrorImage('Too many API requests – please wait a few seconds and try again', 429);
                }

                return self::generateErrorImage('API error. Expected code ' . $exception->getExpected() . ', got ' . $exception->getActualCode());
            } catch (HypixelPHPException $exception) {
                return self::generateErrorImage('Unknown: ' . $exception->getMessage());
            }
        }

        /**
         * @param     $error
         * @param int $statusCode
         *
         * @param int $width
         * @param int $height
         *
         * @return Response
         */
        public static function generateErrorImage($error, $statusCode = 500, $width = 740, $height = 160): Response {
            $image = self::getImage($width, $height);
            $box   = new Box($image);
            $box->setFontFace(resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf'));
            $box->setFontColor(new Color(255, 0, 0));
            $box->setFontSize($height / 3);
            $box->setBox(5, 0, $width - 5, $height - 5);
            $box->setTextAlign('center', 'top');
            $box->draw('Something went wrong');

            $box->setBox(5, $height / 3 + 10, $width - 5, $height - 5);
            $box->setFontSize($height / 6);
            $box->setFontColor(new Color(0, 0, 0));
            $box->draw($error);

            /** @var Response $response */
            $response = Image::make($image)->response('png');
            $response->setStatusCode($statusCode);

            return $response;
        }

        /**
         * @param $width
         * @param $height
         *
         * @return false|resource
         */
        public static function getImage($width, $height) {
            $image       = imagecreatetruecolor($width, $height);
            $transparent = imagecolorallocatealpha($image, 250, 100, 100, config('signatures.signature_debug_background') ? 0 : self::FULLY_TRANSPARENT);
            imagefill($image, 0, 0, $transparent);
            imagesavealpha($image, true);
            return $image;
        }

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         */
        abstract protected function signature(Request $request, Player $player): Response;

        /**
         * Add a watermark referencing the site's name in the bottom right of the supplied image
         *
         * @param     $image
         * @param     $font
         * @param     $imageWidth
         * @param     $imageHeight
         * @param int $size
         */
        protected function addWatermark($image, $font, $imageWidth, $imageHeight, $size = 16): void {
            $grey = imagecolorallocate($image, 203, 203, 203);

            $watermarkBoundingBox = imagettfbbox($size, 0, $font, config('signatures.watermark'));
            imagettftext($image, $size, 0, $imageWidth - $watermarkBoundingBox[4], $imageHeight - $watermarkBoundingBox[3], $grey, $font, config('signatures.watermark'));
        }
    }
