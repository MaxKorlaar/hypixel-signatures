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
    use Plancke\HypixelPHP\classes\gameType\GameTypes;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class GeneralSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class GeneralSignatureController extends BaseSignature {

        /**
         * @param $image
         *
         * @return array
         */
        protected static function getColours($image): array {
            $black  = imagecolorallocate($image, 0, 0, 0);
            $purple = imagecolorallocate($image, 204, 0, 204);
            $blue   = imagecolorallocate($image, 0, 204, 204);
            return [$black, $purple, $blue];
        }

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         * @throws HypixelPHPException
         */
        protected function signature(Request $request, Player $player): Response {
            $image = BaseSignature::getImage(740, 160);
            [$black, $purple, $blue] = self::getColours($image);
            $fontSourceSansProLight = resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf');

            $karma          = $player->getInt('karma', 0);
            $vanityTokens   = $player->getInt('vanityTokens', 0);
            $mostRecentGame = $player->get('mostRecentGameType', 'None');
            $username       = $player->getName();

            $rank       = $player->getRank(false);
            $rankColour = $rank->getColor();
            $rankName   = $rank->getCleanName();
            if ($rankName === 'DEFAULT') {
                $rankName = 'Player';
            }
            $rankNameWithColour = $rankColour . $rankName;

            $lastgameType = GameTypes::fromEnum($mostRecentGame);
            if ($lastgameType !== null) {
                $mostRecentGame = $lastgameType->getName();
            }

            if ($request->has('no_3d_avatar')) {
                [$avatarWidth, $textX, $textBeneathAvatarX] = $this->get2dAvatar($player, $image);
            } else {
                [$avatarWidth, $textX, $textBeneathAvatarX] = $this->get3dAvatar($player, $image);
            }

            if ($request->has('guildTag')) {
                $guildTag = '§7[' . $player->getGuildTag() . ']';
                if ($guildTag === '§7[]') {
                    $guildTag = '§7[-]';
                }
                ColourHelper::minecraftStringToTTFText($image, $fontSourceSansProLight, 25, $textX, 14, '§0' . $username . ' ' . $guildTag);
            } else {
                imagettftext($image, 25, 0, $textX, 30, $black, $fontSourceSansProLight, $username);
            }

            $linesY = [60, 95, 130]; // Y starting points of the various text lines

            ColourHelper::minecraftStringToTTFText($image, $fontSourceSansProLight, 20, $textX, 44, $rankNameWithColour); // Rank name (coloured)

            imagettftext($image, 20, 0, $textX, $linesY[1], $purple, $fontSourceSansProLight, number_format($karma) . ' karma'); // Amount of karma

            imagettftext($image, 20, 0, 380, $linesY[0], $black, $fontSourceSansProLight, 'Level ' . $player->getLevel()); // Network level

            imagettftext($image, 20, 0, 380, $linesY[1], $black, $fontSourceSansProLight, 'Daily Reward High Score: ' . $player->getInt('rewardHighScore')); // Daily reward high score

            imagettftext($image, 20, 0, $textBeneathAvatarX, $linesY[2], $blue, $fontSourceSansProLight, $vanityTokens . ' Hypixel Credits'); // Hypixel Credits

            imagettftext($image, 20, 0, 380, $linesY[2], $black, $fontSourceSansProLight, 'Recently played: ' . $mostRecentGame); // Last game played

            $this->addWatermark($image, $fontSourceSansProLight, 740, 160); // Watermark/advertisement

            return Image::make($image)->response('png');
        }

    }
