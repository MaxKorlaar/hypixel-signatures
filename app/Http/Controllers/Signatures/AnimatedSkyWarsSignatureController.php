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
    use Exception;
    use GifCreator\AnimGif;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Plancke\HypixelPHP\classes\gameType\GameTypes;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\player\GameStats;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class AnimatedSkyWarsSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class AnimatedSkyWarsSignatureController extends BaseSignature {
        private string $fontSourceSansProLight;
        private int $black;
        private int $grey;

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         * @throws HypixelPHPException
         * @throws Exception
         */
        protected function signature(Request $request, Player $player): Response {
            $generalImage = self::getImage(650, 160);

            $this->black                  = imagecolorallocate($generalImage, 0, 0, 0);
            $this->grey                   = imagecolorallocate($generalImage, 203, 203, 203);
            $this->fontSourceSansProLight = resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf');

            $mainStats = $player->getStats();
            /** @var GameStats $stats */
            $stats = $mainStats->getGameFromID(GameTypes::SKYWARS);

            /*
             * General SkyWars statistics
             */

            $wins           = $stats->getInt('wins');
            $kills          = $stats->getInt('kills');
            $deaths         = $stats->getInt('deaths');
            $losses         = $stats->getInt('losses');
            $levelFormatted = $stats->get('levelFormatted', '00');

            $kd = $this->getKD($kills, $deaths);

            if ($wins !== 0) {
                $winsPercentage = round(($wins / ($wins + $losses)) * 100, 2);
            } else {
                $winsPercentage = 0;
            }

            [$textX, $textBeneathAvatarX] = $this->addBasicItems($request, $player, $generalImage, 'General SkyWars statistics');

            $this->addStatistics($generalImage, $textX, $textBeneathAvatarX, $wins, $kills, $kd, false, $winsPercentage);

            ColourHelper::minecraftStringToTTFText($generalImage, $this->fontSourceSansProLight, 20, $textBeneathAvatarX, 114, '§0Level: ' . mb_substr($levelFormatted, 0, -1)); // SkyWars level

            $this->addWatermark($generalImage, $this->fontSourceSansProLight, 650, 160); // Watermark/advertisement

            /*
             * Solo SkyWars statistics
             */

            $soloImage = self::getImage(650, 160);

            [$textX, $textBeneathAvatarX] = $this->addBasicItems($request, $player, $soloImage, 'Solo SkyWars statistics');

            $wins            = $stats->getInt('wins_solo');
            $kills           = $stats->getInt('kills_solo');
            $deaths          = $stats->getInt('deaths_solo');
            $losses          = $stats->getInt('losses_solo');
            $survivedPlayers = $stats->getInt('survived_players_solo');

            $kd = $this->getKD($kills, $deaths);

            if ($wins !== 0) {
                $winsPercentage = round(($wins / ($wins + $losses)) * 100, 2);
            } else {
                $winsPercentage = 0;
            }

            $this->addStatistics($soloImage, $textX, $textBeneathAvatarX, $wins, $kills, $kd, $survivedPlayers, $winsPercentage);
            $this->addWatermark($soloImage, $this->fontSourceSansProLight, 650, 160); // Watermark/advertisement

            /*
             * Mega SkyWars statistics
             */

            $megaImage = self::getImage(650, 160);

            [$textX, $textBeneathAvatarX] = $this->addBasicItems($request, $player, $megaImage, 'Mega SkyWars statistics');

            $wins            = $stats->getInt('wins_mega');
            $kills           = $stats->getInt('kills_mega');
            $deaths          = $stats->getInt('deaths_mega');
            $losses          = $stats->getInt('losses_mega');
            $survivedPlayers = $stats->getInt('survived_players_mega');

            $kd = $this->getKD($kills, $deaths);

            if ($wins !== 0) {
                $winsPercentage = round(($wins / ($wins + $losses)) * 100, 2);
            } else {
                $winsPercentage = 0;
            }

            $this->addStatistics($megaImage, $textX, $textBeneathAvatarX, $wins, $kills, $kd, $survivedPlayers, $winsPercentage);
            $this->addWatermark($megaImage, $this->fontSourceSansProLight, 650, 160); // Watermark/advertisement

            /*
             * Teams SkyWars statistics
             */

            $teamsImage = self::getImage(650, 160);

            [$textX, $textBeneathAvatarX] = $this->addBasicItems($request, $player, $teamsImage, 'Teams SkyWars statistics');

            $wins            = $stats->getInt('wins_team');
            $kills           = $stats->getInt('kills_team');
            $deaths          = $stats->getInt('deaths_team');
            $losses          = $stats->getInt('losses_team');
            $survivedPlayers = $stats->getInt('survived_players_team');

            $kd = $this->getKD($kills, $deaths);

            if ($wins !== 0) {
                $winsPercentage = round(($wins / ($wins + $losses)) * 100, 2);
            } else {
                $winsPercentage = 0;
            }

            $this->addStatistics($teamsImage, $textX, $textBeneathAvatarX, $wins, $kills, $kd, $survivedPlayers, $winsPercentage);
            $this->addWatermark($teamsImage, $this->fontSourceSansProLight, 650, 160); // Watermark/advertisement

            /*
             * Ranked SkyWars statistics
             */

            $rankedImage = self::getImage(650, 160);

            [$textX, $textBeneathAvatarX] = $this->addBasicItems($request, $player, $rankedImage, 'Ranked SkyWars statistics');

            $wins            = $stats->getInt('wins_ranked');
            $kills           = $stats->getInt('kills_ranked');
            $deaths          = $stats->getInt('deaths_ranked');
            $losses          = $stats->getInt('losses_ranked');
            $survivedPlayers = $stats->getInt('survived_players_ranked');

            $kd = $this->getKD($kills, $deaths);

            if ($wins !== 0) {
                $winsPercentage = round(($wins / ($wins + $losses)) * 100, 2);
            } else {
                $winsPercentage = 0;
            }

            $this->addStatistics($rankedImage, $textX, $textBeneathAvatarX, $wins, $kills, $kd, $survivedPlayers, $winsPercentage);
            $this->addWatermark($rankedImage, $this->fontSourceSansProLight, 650, 160); // Watermark/advertisement

            $gif = new AnimGif();

            $duration = 200;

            $gif->create([$generalImage, $soloImage, $megaImage, $teamsImage, $rankedImage], [$duration, $duration, $duration, $duration, $duration]);

            imagedestroy($generalImage);
            imagedestroy($soloImage);
            imagedestroy($megaImage);
            imagedestroy($teamsImage);
            imagedestroy($rankedImage);

            $response = new Response($gif->get(), 200, [
                'Content-Type' => 'image/gif'
            ]);

            return $response->setCache([
                'public'  => true,
                'max_age' => 600
            ]);
        }

        /**
         * @inheritDoc
         */
        public static function getImage($width, $height) {
            $image       = imagecreatetruecolor($width, $height);
            $transparent = imagecolorallocatealpha($image, 250, 250, 250, 0);
            imagefill($image, 0, 0, $transparent);
            imagesavealpha($image, true);
            return $image;
        }

        /**
         * @param $kills
         * @param $deaths
         *
         * @return float|string
         */
        private function getKD(int $kills, int $deaths) {
            if ($deaths !== 0) {
                return round($kills / $deaths, 2);
            }

            return 'None';
        }

        /**
         * @param Request  $request
         * @param Player   $player
         *
         * @param resource $image
         *
         * @param string   $title
         *
         * @return array
         * @throws HypixelPHPException
         */
        private function addBasicItems(Request $request, Player $player, &$image, string $title) {
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
                $usernameBoundingBox = ColourHelper::minecraftStringToTTFText($image, $this->fontSourceSansProLight, 25, $textX, 14, '§0' . $player->getName() . ' ' . $guildTag);
            } else {
                $usernameBoundingBox = imagettftext($image, 25, 0, $textX, 30, $this->black, $this->fontSourceSansProLight, $player->getName());
            }

            imagettftext($image, 17, 0, $usernameBoundingBox[2] + 10, 30, $this->grey, $this->fontSourceSansProLight, $title);

            $rank       = $player->getRank(false);
            $rankColour = $rank->getColor();
            $rankName   = $rank->getCleanName();

            if ($rankName === 'DEFAULT') {
                $rankName = 'Player';
            }

            $rankNameWithColour = $rankColour . $rankName;

            ColourHelper::minecraftStringToTTFText($image, $this->fontSourceSansProLight, 20, $textX, 44, $rankNameWithColour); // Rank name (coloured)

            return [$textX, $textBeneathAvatarX];
        }

        /**
         * @param           $image
         * @param int       $textX
         * @param int       $textBeneathAvatarX
         * @param int       $wins
         * @param int       $kills
         * @param           $kd
         * @param int|false $survived
         * @param int       $winsPercentage
         */
        private function addStatistics(&$image, int $textX, int $textBeneathAvatarX, int $wins, int $kills, $kd, $survived, int $winsPercentage): void {
            $linesY = [60, 95, 130]; // Y starting points of the various text lines

            imagettftext($image, 20, 0, $textX, $linesY[1], $this->black, $this->fontSourceSansProLight, number_format($wins) . ' wins'); // Total wins

            imagettftext($image, 20, 0, 350, $linesY[0], $this->black, $this->fontSourceSansProLight, number_format($kills) . ' kills'); // Total kills

            imagettftext($image, 20, 0, 350, $linesY[1], $this->black, $this->fontSourceSansProLight, 'KD: ' . $kd); // kill/death ratio

            if ($survived !== false) {
                imagettftext($image, 20, 0, $textBeneathAvatarX, $linesY[2], $this->black, $this->fontSourceSansProLight, 'Survived ' . number_format($survived) . ' players');
            }

            imagettftext($image, 20, 0, 350, $linesY[2], $this->black, $this->fontSourceSansProLight, "Wins percentage: {$winsPercentage}%"); // Percentage of games won
        }

    }
