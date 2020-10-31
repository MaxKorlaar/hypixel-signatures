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

    use App\Utilities\ColourHelper;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Plancke\HypixelPHP\classes\gameType\GameTypes;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\player\GameStats;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class CopsAndCrimsSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class CopsAndCrimsSignatureController extends BaseSignature {

        /**
         * @inheritDoc
         * @throws HypixelPHPException
         */
        protected function signature(Request $request, Player $player): Response {
            $image                  = BaseSignature::getImage(500, 160);
            $black                  = imagecolorallocate($image, 0, 0, 0);
            $grey                   = imagecolorallocate($image, 203, 203, 203);
            $fontSourceSansProLight = resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf');

            $username           = $player->getName();
            $rankNameWithColour = $this->getColouredRankName($player);

            $mainStats = $player->getStats();
            /** @var GameStats $stats */
            $stats = $mainStats->getGameFromID(GameTypes::MCGO);

            $kills         = $stats->getInt('kills');
            $deaths        = $stats->getInt('deaths');
            $gameWins      = $stats->getInt('game_wins');
            $roundWins     = $stats->getInt('round_wins');
            $headshotKills = $stats->getInt('headshot_kills');

            if ($deaths !== 0) {
                $kd = round($kills / $deaths, 2);
            } else {
                $kd = 'None';
            }

            if ($request->has('no_3d_avatar')) {
                [, $textX, $textBeneathAvatarX] = $this->get2dAvatar($player, $image);
            } else {
                [, $textX, $textBeneathAvatarX] = $this->get3dAvatar($player, $image);
            }

            if ($request->has('guildTag')) {
                $guildTag = '§7[' . $player->getGuildTag() . ']';
                if ($guildTag === '§7[]') {
                    $guildTag = '§7[-]';
                }
                $usernameBoundingBox = ColourHelper::minecraftStringToTTFText($image, $fontSourceSansProLight, 25, $textX, 14, '§0' . $username . ' ' . $guildTag);
            } else {
                $usernameBoundingBox = imagettftext($image, 25, 0, $textX, 30, $black, $fontSourceSansProLight, $username);
            }

            imagettftext($image, 17, 0, $usernameBoundingBox[2] + 10, 30, $grey, $fontSourceSansProLight, 'Cops and Crims statistics');

            $linesY = [60, 95, 130]; // Y starting points of the various text lines

            ColourHelper::minecraftStringToTTFText($image, $fontSourceSansProLight, 20, $textX, 44, $rankNameWithColour); // Rank name (coloured)

            imagettftext($image, 20, 0, $textX, $linesY[1], $black, $fontSourceSansProLight, number_format($kills) . ' kills');

            imagettftext($image, 20, 0, 275, $linesY[0], $black, $fontSourceSansProLight, number_format($roundWins) . ' round wins');

            imagettftext($image, 20, 0, 275, $linesY[1], $black, $fontSourceSansProLight, 'KD: ' . $kd);

            imagettftext($image, 20, 0, $textBeneathAvatarX, $linesY[2], $black, $fontSourceSansProLight, number_format($gameWins) . ' wins');

            imagettftext($image, 20, 0, 275, $linesY[2], $black, $fontSourceSansProLight, number_format($headshotKills) . ' headshots');

            $this->addWatermark($image, $fontSourceSansProLight, 500, 160); // Watermark/advertisement

            return Image::make($image)->response('png')->setCache([
                'public'  => true,
                'max_age' => 600
            ]);
        }
    }
