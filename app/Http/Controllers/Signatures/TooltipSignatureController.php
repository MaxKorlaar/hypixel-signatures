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
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class TooltipSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    final class TooltipSignatureController extends BaseSignature {

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         */
        protected function signature(Request $request, Player $player): Response {
            $image = imagecreatefrompng(resource_path('images/Tooltip.png'));

            $fontMinecraftia = resource_path('fonts/minecraftia/Minecraftia.ttf');
            $green           = imagecolorallocate($image, 85, 255, 85);

            $rank       = $player->getRank(false);
            $rankPrefix = $rank->getPrefix($player);

            $rankPrefix = substr($rankPrefix, 0, 3) . substr($rankPrefix, 4, -1);

            imagettftext($image, 14, 0, 10, 25, $green, $fontMinecraftia, 'Character Information');

            $spacing  = 22;
            $start    = 50 - 16;
            $fontSize = 13;

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start, '§7Rank: ' . $rankPrefix); // Rank

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start + $spacing, '§7Level: §6' . ($player->getLevel())); // Level

            $level     = $player->getLevel();
            $expNeeded = ($level - 1) * 2500 + 10000;
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start + 2 * $spacing, '§7Experience until next Level: §6' . $expNeeded); // Experience until next level

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start + 3 * $spacing, '§7Hypixel Credits: §b' . $player->getInt('vanityTokens')); // Hypixel Credits

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start + 4 * $spacing, '§7Karma: §d' . number_format($player->getInt('karma'))); // Karma

            return Image::make($image)->response('png');
        }

    }
