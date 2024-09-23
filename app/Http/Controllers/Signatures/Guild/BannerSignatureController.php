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

    namespace App\Http\Controllers\Signatures\Guild;

    use App\Http\Controllers\Signatures\BaseSignature;
    use App\Utilities\Minecraft\Banner;
    use GDText\Box;
    use GDText\Color;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Intervention\Image\Laravel\Facades\Image;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\guild\Guild;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class BannerSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class BannerSignatureController extends BaseSignature {

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         * @throws HypixelPHPException
         */
        protected function signature(Request $request, Player $player): Response {
            $guild = $player->getGuild();

            if (!$guild instanceof Guild) {
                return self::generateErrorImage('An error has occurred while trying to fetch guild data for ' . $player->getName() . '. They may not be part of a guild yet. Please try again later.');
            }

            $bannerData           = $guild->getBanner();
            $fontMinecraftRegular = resource_path('fonts/Minecraft/1_Minecraft-Regular.otf');

            if (!empty($bannerData)) {
                $imageWidth  = 500;
                $imageHeight = 240;
                $image       = BaseSignature::getImage($imageWidth, $imageHeight);

                $banner      = new Banner($bannerData);
                $bannerImage = $banner->generate();

                $bannerWidth  = imagesx($bannerImage);
                $bannerHeight = imagesy($bannerImage);
                $size         = 6;

                $textX = ($bannerWidth * $size) + 5;

                imagecopyresized($image, $bannerImage, 0, 0, 0, 0, $bannerWidth * $size, $bannerHeight * $size, $bannerWidth, $bannerHeight);
                imagedestroy($bannerImage);
            } else {
                $imageWidth  = 500;
                $imageHeight = 130;
                $image       = BaseSignature::getImage($imageWidth, $imageHeight);

                [, $textX] = $this->get3dAvatar($player, $image);
            }

            $box = new Box($image);
            $box->setFontFace($fontMinecraftRegular);
            $box->setFontColor(new Color(0, 0, 0));
            $box->setTextShadow(new Color(0, 0, 0, 100), 2, 2);
            $box->setFontSize(50);
            $box->setBox($textX, 0, $imageWidth - $textX, $imageHeight);
            $box->setTextAlign('center', 'center');
            $box->draw($guild->getName());

            return response(Image::read($image)->encodeByExtension('png'))
                ->header('Content-Type', 'image/png')
                ->setCache([
                    'public'  => true,
                    'max_age' => 600
                ]);
        }
    }
