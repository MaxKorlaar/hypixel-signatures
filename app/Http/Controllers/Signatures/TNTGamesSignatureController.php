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
     * Class TNTGamesSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class TNTGamesSignatureController extends BaseSignature {

        /**
         * @inheritDoc
         * @throws HypixelPHPException
         */
        protected function signature(Request $request, Player $player): Response {
            $image                  = BaseSignature::getImage(540, 175);
            $black                  = imagecolorallocate($image, 0, 0, 0);
            $grey                   = imagecolorallocate($image, 203, 203, 203);
            $fontSourceSansProLight = resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf');

            $username           = $player->getName();
            $rankNameWithColour = $this->getColouredRankName($player);

            $mainStats = $player->getStats();
            /** @var GameStats $stats */
            $stats = $mainStats->getGameFromID(GameTypes::TNTGAMES);

            $bowspleefWins = $stats->getInt('wins_bowspleef');
            $wizardsWins   = $stats->getInt('wins_capture'); // Wizards
            $tntTagWins    = $stats->getInt('wins_tntag');
            $tntRunWins    = $stats->getInt('wins_tntrun');
            $pvpRunWins    = $stats->getInt('wins_pvprun');

            $tntrunRecord = '00:00';
            $pvprunRecord = '00:00';

            if ($stats->getInt('record_tntrun') > 0) {
                $mins = floor($stats->getInt('record_tntrun') / 60);
                $secs = floor($stats->getInt('record_tntrun') % 60);

                if (strlen($mins) < 2) {
                    $mins = '0' . $mins;
                }

                if (strlen($secs) < 2) {
                    $secs = '0' . $secs;
                }

                $tntrunRecord = $mins . ':' . $secs;
            }

            if ($stats->getInt('record_pvprun') > 0) {
                $mins = floor($stats->getInt('record_pvprun') / 60);
                $secs = floor($stats->getInt('record_pvprun') % 60);

                if (strlen($mins) < 2) {
                    $mins = '0' . $mins;
                }

                if (strlen($secs) < 2) {
                    $secs = '0' . $secs;
                }

                $pvprunRecord = $mins . ':' . $secs;
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

            imagettftext($image, 17, 0, $usernameBoundingBox[2] + 10, 30, $grey, $fontSourceSansProLight, 'TNT Games statistics');

            $linesY = [60, 90, 120, 150]; // Y starting points of the various text lines

            ColourHelper::minecraftStringToTTFText($image, $fontSourceSansProLight, 20, $textX, 44, $rankNameWithColour); // Rank name (coloured)

            imagettftext($image, 20, 0, $textX, $linesY[1], $black, $fontSourceSansProLight, number_format($bowspleefWins) . ' Bowspleef wins');

            imagettftext($image, 20, 0, 280, $linesY[0], $black, $fontSourceSansProLight, number_format($tntTagWins) . ' TNT Tag wins');

            imagettftext($image, 20, 0, 280, $linesY[1], $black, $fontSourceSansProLight, number_format($wizardsWins) . ' Wizards wins');

            imagettftext($image, 20, 0, $textBeneathAvatarX, $linesY[2], $black, $fontSourceSansProLight, number_format($tntRunWins) . ' TNT Run wins');

            imagettftext($image, 20, 0, 280, $linesY[2], $black, $fontSourceSansProLight, 'TNT Run record: ' . $tntrunRecord);

            imagettftext($image, 20, 0, $textBeneathAvatarX, $linesY[3], $black, $fontSourceSansProLight, number_format($pvpRunWins) . ' PVP Run wins');

            imagettftext($image, 20, 0, 280, $linesY[3], $black, $fontSourceSansProLight, 'PVP Run record: ' . $pvprunRecord);

            $this->addWatermark($image, $fontSourceSansProLight, 540, 175); // Watermark/advertisement

            return Image::make($image)->response('png')->setCache([
                'public'  => true,
                'max_age' => 600
            ]);
        }
    }
