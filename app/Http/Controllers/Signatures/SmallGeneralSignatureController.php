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

    use App\Utilities\ColourHelper;
    use App\Utilities\MinecraftAvatar\ThreeDAvatar;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class SmallGeneralSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    final class SmallGeneralSignatureController extends GeneralSignatureController {

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         * @throws HypixelPHPException
         */
        protected function signature(Request $request, Player $player): Response {
            $image = BaseSignature::getImage(630, 100);
            [$black, $purple, $blue] = self::getColours($image);
            $fontSourceSansProLight = resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf');

            $karma        = $player->get('karma', 0);
            $vanityTokens = $player->get('vanityTokens', 0);

            if ($request->has('no_3d_avatar')) {
                $avatarWidth = 0;
                $textX       = $avatarWidth + 4;
            } else {
                $threedAvatar = new ThreeDAvatar();
                $avatarImage  = $threedAvatar->getThreeDSkinFromCache($player->getUUID(), 3, 30, false, true, true);

                $avatarWidth = imagesx($avatarImage);
                $textX       = $avatarWidth + 4;

                imagecopy($image, $avatarImage, 0, 0, 0, 0, imagesx($avatarImage), imagesy($avatarImage));
                imagedestroy($avatarImage);
            }

            ColourHelper::minecraftStringToTTFText($image, $fontSourceSansProLight, 21, $textX, 5, $player->getRawFormattedName(true, $request->has('guildTag')));

            $linesY = [50, 75]; // Y starting points of the various text lines

            imagettftext($image, 19, 0, $textX, $linesY[0], $blue, $fontSourceSansProLight, $vanityTokens . ' Hypixel Credits'); // Hypixel Credits

            imagettftext($image, 19, 0, $textX, $linesY[1], $purple, $fontSourceSansProLight, number_format($karma) . ' karma'); // Amount of karma

            imagettftext($image, 19, 0, 315, $linesY[0], $black, $fontSourceSansProLight, 'Level ' . $player->getLevel()); // Network level

            imagettftext($image, 19, 0, 315, $linesY[1], $black, $fontSourceSansProLight, 'Daily Reward High Score: ' . $player->getInt('rewardHighScore')); // Daily reward high score

            $this->addWatermark($image, $fontSourceSansProLight, 630, 100, 14); // Watermark/advertisement

            return Image::make($image)->response('png')->setCache([
                'public'  => true,
                'max_age' => 600
            ]);
        }

    }
