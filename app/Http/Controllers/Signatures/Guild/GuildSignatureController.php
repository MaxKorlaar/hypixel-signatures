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
    use App\Utilities\ColourHelper;
    use App\Utilities\Minecraft\Banner;
    use GDText\Box;
    use GDText\Color;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    use Intervention\Image\Laravel\Facades\Image;
    use Plancke\HypixelPHP\classes\gameType\GameTypes;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\guild\Guild;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class GuildSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class GuildSignatureController extends BaseSignature {

        /**
         *
         * @throws HypixelPHPException
         */
        protected function signature(Request $request, Player $player): Response {
            $guild = $player->getGuild();

            if (!$guild instanceof Guild) {
                return self::generateErrorImage('An error has occurred while trying to fetch guild data for ' . $player->getName() . '. They may not be part of a guild yet. Please try again later.');
            }

            $bannerData = $guild->getBanner();

            if (!empty($bannerData)) {
                $imageWidth  = 740;
                $imageHeight = 200;

                $image = BaseSignature::getImage($imageWidth, $imageHeight);

                $banner      = new Banner($bannerData);
                $bannerImage = $banner->generate();

                $bannerWidth  = imagesx($bannerImage);
                $bannerHeight = imagesy($bannerImage);
                $size         = 5;

                $textX = ($bannerWidth * $size) + 5;

                imagecopyresized($image, $bannerImage, 0, 0, 0, 0, $bannerWidth * $size, $bannerHeight * $size, $bannerWidth, $bannerHeight);
                imagedestroy($bannerImage);
            } else {
                $imageWidth  = 740;
                $imageHeight = 200;

                $image = BaseSignature::getImage($imageWidth, $imageHeight);

                [, $textX] = $this->get3dAvatar($player, $image);
            }

            $black                  = imagecolorallocate($image, 0, 0, 0);
            $fontSourceSansProLight = resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf');

            $guildTag = '';

            if ($guild->getTag() !== null) {
                $guildTag = $guild->getTagColor() . ' [' . $guild->getTag() . ']';
            }

            ColourHelper::minecraftStringToTTFText($image, $fontSourceSansProLight, 25, $textX, 14, '§0' . $guild->getName() . $guildTag);

            $linesY = [60, 90, 120, 150]; // Y starting points of the various text lines

            $memberList = $guild->getMemberList()->getList();

            uksort($memberList, static function ($a, $b) {
                if (Str::is(['guildmaster', 'guild master'], strtolower($b))) {
                    return 1;
                }

                return 0;
            });

            $highestRank = array_shift($memberList);

            $guildMaster       = $highestRank[0];
            $guildMasterPlayer = $guildMaster->getPlayer();

            if ($guildMasterPlayer instanceof Player) {
                imagettftext($image, 20, 0, $textX, $linesY[0], $black, $fontSourceSansProLight, 'Guild Master: ' . $guildMasterPlayer->getName());
            } else {
                imagettftext($image, 20, 0, $textX, $linesY[0], $black, $fontSourceSansProLight, 'Guild Master: (unknown)');
            }

            imagettftext($image, 20, 0, $textX, $linesY[1], $black, $fontSourceSansProLight, 'Members: ' . $guild->getMemberCount() . '/' . '125');

            $games = new Collection($guild->getExpByGameType());

            $mostActiveGames = $games->sortDesc()->slice(0, 3)->map(static function ($xp, $gameName) {
                $gameType = GameTypes::fromEnum($gameName);

                if ($gameType === null) {
                    return ucfirst(strtolower($gameName));
                }

                return $gameType->getName();
            });

            imagettftext($image, 20, 0, $textX, $linesY[2], $black, $fontSourceSansProLight, 'Most active games: ' . $mostActiveGames->join(', '));

            if (trim($guild->getDescription()) !== '') {
                $box = new Box($image);
                $box->setFontFace($fontSourceSansProLight);
                $box->setFontColor(new Color(0, 0, 0));
                $box->setFontSize(20 / 0.75);
                $box->setLineHeight(1);
                $box->setBox($textX, $linesY[3] - 20, $imageWidth - $textX, $imageHeight - $linesY[3] - 20);
                $box->setTextAlign('left', 'top');
                $box->draw('Description: ' . $guild->getDescription());
            }

            return response(Image::read($image)->encodeByExtension('png'))
                ->header('Content-Type', 'image/png')
                ->setCache([
                    'public'  => true,
                    'max_age' => 600
                ]);
        }
    }
