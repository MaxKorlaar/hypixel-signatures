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

    namespace App\Http\Controllers\Signatures\SkyBlock;

    use App\Exceptions\HypixelFetchException;
    use App\Exceptions\SkyBlockEmptyProfileException;
    use App\Http\Controllers\Signatures\BaseSignature;
    use App\Utilities\MinecraftAvatar\ThreeDAvatar;
    use App\Utilities\SkyBlock\SkyBlockStatsDataParser;
    use GDText\Box;
    use GDText\Color;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class SkyBlockSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class MinionsSignatureController extends SkyBlockSignatureController {

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         */
        protected function signature(Request $request, Player $player): Response {
            if ($this->profileId === null) {
                return self::generateErrorImage('No SkyBlock profile has been given', 400);
            }

            try {
                $minions = SkyBlockStatsDataParser::getSkyBlockMinions($player, $this->profileId)->where('max_level', '>', 0)->sortByDesc('max_level');
            } catch (HypixelFetchException $exception) {
                return self::generateErrorImage('An error has occurred while trying to fetch this SkyBlock profile. Please try again later.');
            } catch (SkyBlockEmptyProfileException $e) {
                return self::generateErrorImage('This SkyBlock profile has no data. It may have been deleted.');
            }

            if ($minions->isEmpty()) {
                return self::generateErrorImage('This SkyBlock profile does not have any minions.', 200);
            }

            $perRow = (int)$request->input('per_row', 15);

            if (5 < $perRow && $perRow > 50) {
                return self::generateErrorImage('The amount of minions per row must be between 5 and 50', 400);
            }

            $size = (int)$request->input('size', $minions->count() > (2 * $perRow) ? 3 : 5);

            if (1 < $size && $size > 5) {
                return self::generateErrorImage('The given size is not supported for this image. Size must be between 1 and 5', 400);
            }

            $minionsAndImages = [];

            $currentX              = 0;
            $currentY              = 0;
            $avatarWidth           = 0;
            $avatarHeight          = 0;
            $distanceBetweenImages = $size;
            $textDistance          = 0;

            foreach ($minions as $minion) {
                $threedAvatar       = new ThreeDAvatar();
                $minionImage        = $threedAvatar->getThreeDSkinFromCache($minion['texture_name'], $size, 45, true, true, true, -30);
                $avatarWidth        = imagesx($minionImage);
                $avatarHeight       = imagesy($minionImage);
                $minionsAndImages[] = ['image' => $minionImage] + $minion->toArray();
            }

            $rowCount    = ceil(count($minions) / $perRow);
            $imageWidth  = min(count($minions), $perRow) * ($avatarWidth + $distanceBetweenImages);
            $imageHeight = $avatarHeight * 1.5 * $rowCount + 15;

            $image = BaseSignature::getImage($imageWidth, $imageHeight);
            $black = new Color(0, 0, 0);
            $gold  = new Color(221, 152, 14);

            $fontMinecraftRegular = resource_path('fonts/Minecraft/1_Minecraft-Regular.otf');

            foreach ($minionsAndImages as $index => $minion) {
                if ($index % $perRow === 0 && $index > 0) {
                    $currentX = 0;
                    $currentY += $avatarHeight * 1.5;

                    if (ceil(($index + 1) / $perRow) === $rowCount) { // Calculate x coordinate for the final row, centering the images
                        $remainingMinionsCount = count($minions) % $perRow;
                        $remainingMinionsWidth = (($avatarWidth + $distanceBetweenImages) * $remainingMinionsCount) - $distanceBetweenImages;
                        $currentX              = $imageWidth / 2 - $remainingMinionsWidth / 2;
                    }
                }

                $minionImage = $minion['image'];
                imagecopy($image, $minionImage, $currentX, $currentY, 0, 0, imagesx($minionImage), imagesy($minionImage));
                imagedestroy($minionImage);

                $box = new Box($image);
                $box->setFontFace($fontMinecraftRegular);
                $box->setFontColor($minion['max_level'] === 11 ? $gold : $black);
                $box->setFontSize($avatarHeight / 2.4);
                $box->setBox($currentX, $currentY + $avatarHeight + $textDistance, $avatarWidth, $avatarHeight / 2);
                $box->setTextAlign('center', 'top');
                $box->draw($minion['max_level']);

                $currentX += $avatarWidth + $distanceBetweenImages;
            }

            $this->addWatermark($image, $fontMinecraftRegular, $imageWidth, $imageHeight, $size * 2.5); // Watermark/advertisement

            return Image::make($image)->response('png')->setCache([
                'public'  => true,
                'max_age' => 600
            ]);
        }
    }
