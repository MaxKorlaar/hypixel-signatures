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

    namespace App\Http\Controllers\Signatures;

    use App\Exceptions\HypixelFetchException;
    use App\Exceptions\SkyBlockEmptyProfileException;
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
    class SkyBlockPetsSignatureController extends SkyBlockSignatureController {

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
                $pets = SkyBlockStatsDataParser::getSkyBlockPets($player, $this->profileId);
            } catch (HypixelFetchException $exception) {
                return self::generateErrorImage('An error has occurred while trying to fetch this SkyBlock profile. Please try again later.');
            } catch (SkyBlockEmptyProfileException $e) {
                return self::generateErrorImage('This SkyBlock profile has no data. It may have been deleted.');
            }

            if ($pets->isEmpty()) {
                return self::generateErrorImage('This SkyBlock profile does not have any pets.', 200);
            }

            $petsAndImages = [];

            $currentX              = 0;
            $currentY              = 0;
            $avatarWidth           = 0;
            $avatarHeight          = 0;
            $size                  = 5;
            $distanceBetweenImages = $size;
            $textDistance          = 0;

            // By default, pets are sorted based on rarity, and the active pet is the first pet.
            // This can be overridden using the sort=levels parameter.

            if ($request->input('sort') === 'level') {
                $pets = $pets->sortByDesc('level.level');
            }

            foreach ($pets as $pet) {
                $threedAvatar    = new ThreeDAvatar();
                $petImage        = $threedAvatar->getThreeDSkinFromCache($pet['texture_name'], $size, 45, true, true, true, -30);
                $avatarWidth     = imagesx($petImage);
                $avatarHeight    = imagesy($petImage);
                $petsAndImages[] = ['image' => $petImage] + $pet;
            }

            $imageWidth  = count($pets) * ($avatarWidth + $distanceBetweenImages);
            $imageHeight = $avatarHeight * 1.75;

            $image                = BaseSignature::getImage($imageWidth, $imageHeight);
            $black                = new Color(0, 0, 0);
            $fontMinecraftRegular = resource_path('fonts/Minecraft/1_Minecraft-Regular.otf');

            $colors = [
                'legendary' => new Color(221, 152, 14),
                'epic'      => new  Color(170, 0, 170),
                'rare'      => new Color(85, 85, 255),
                'uncommon'  => new Color(60, 189, 60),
                'common'    => new Color(85, 85, 85)
            ];

            foreach ($petsAndImages as $pet) {
                $petImage = $pet['image'];
                imagecopy($image, $petImage, $currentX, $currentY, 0, 0, imagesx($petImage), imagesy($petImage));
                imagedestroy($petImage);

                $box = new Box($image);

                $box->setFontFace($fontMinecraftRegular);
                $box->setFontColor($colors[$pet['rarity']] ?? $black);
                $box->setFontSize($avatarHeight / 2.4);

                if ($pet['active'] && $request->get('highlight_active') !== 'false') {
                    $box->setStrokeSize(round($avatarHeight / 35));
                }

                $box->setBox($currentX, $currentY + $avatarHeight + $textDistance, $avatarWidth, $avatarHeight / 2);
                $box->setTextAlign('center', 'top');
                $box->draw($pet['level']['level']);

                $currentX += $avatarWidth + $distanceBetweenImages;
            }

            $this->addWatermark($image, $fontMinecraftRegular, $imageWidth, $imageHeight, $size * 2.5); // Watermark/advertisement

            return Image::make($image)->response('png')->setCache([
                'public'  => true,
                'max_age' => 600
            ]);
        }


    }
