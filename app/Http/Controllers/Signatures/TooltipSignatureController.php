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

    namespace App\Http\Controllers\Signatures;

    use App\Utilities\ColourHelper;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use Intervention\Image\Laravel\Facades\Image;
    use Plancke\HypixelPHP\responses\player\Player;
    use Plancke\HypixelPHP\util\Leveling;

    /**
     * Class TooltipSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    final class TooltipSignatureController extends BaseSignature {
        /**
         * @param int $imageWidth
         *
         * @param int $imageHeight
         *
         * @return false|resource
         */
        public static function getTooltipImage(int $imageWidth, int $imageHeight) {
            $tooltipImage = imagecreatefrompng(resource_path('images/Tooltip.png'));

            $cornerSize = 10;

            $image = BaseSignature::getImage($imageWidth, $imageHeight);

            $tooltipWidth  = imagesx($tooltipImage);
            $tooltipHeight = imagesy($tooltipImage);

            $copyY = $tooltipHeight - ($cornerSize * 2);

            $timesToCopyY    = floor(($imageHeight - ($cornerSize * 2)) / $copyY);
            $remainingHeight = ($imageHeight - ($cornerSize * 2)) % $copyY;

            $copyPosY = $cornerSize;

            for ($i = 0; $i < $timesToCopyY; $i++) {
                $copyPosY = $cornerSize + ($i * $copyY);
                imagecopy($image, $tooltipImage, 0, $copyPosY, 0, $cornerSize, $cornerSize, $copyY);
                imagecopy($image, $tooltipImage, $imageWidth - $cornerSize, $copyPosY, $tooltipWidth - $cornerSize, $cornerSize, $cornerSize, $copyY);
                $copyPosY += $copyY;
            }

            if ($remainingHeight > 0) {
                imagecopy($image, $tooltipImage, 0, $copyPosY, 0, $cornerSize, $cornerSize, $remainingHeight);
                imagecopy($image, $tooltipImage, $imageWidth - $cornerSize, $copyPosY, $tooltipWidth - $cornerSize, $cornerSize, $cornerSize, $remainingHeight);
            }

            $copyX = $tooltipWidth - ($cornerSize * 2);

            $timesToCopyX   = floor(($imageWidth - ($cornerSize * 2)) / $copyX);
            $remainingWidth = ($imageWidth - ($cornerSize * 2)) % $copyX;

            $copyPosX = $cornerSize;

            for ($i = 0; $i < $timesToCopyX; $i++) {
                $copyPosX = $cornerSize + ($i * $copyX);
                imagecopy($image, $tooltipImage, $copyPosX, 0, $cornerSize, 0, $copyX, $cornerSize);
                imagecopy($image, $tooltipImage, $copyPosX, $imageHeight - $cornerSize, $cornerSize, $tooltipHeight - $cornerSize, $copyX, $cornerSize);
                $copyPosX += $copyX;
            }

            if ($remainingWidth > 0) {
                imagecopy($image, $tooltipImage, $copyPosX, 0, $cornerSize, 0, $remainingWidth, $cornerSize); // Top border
                imagecopy($image, $tooltipImage, $copyPosX, $imageHeight - $cornerSize, $cornerSize, $tooltipHeight - $cornerSize, $remainingWidth, $cornerSize); // Bottom border
            }

            imagecopy($image, $tooltipImage, 0, 0, 0, 0, $cornerSize, $cornerSize); // Top left
            imagecopy($image, $tooltipImage, $imageWidth - $cornerSize, 0, $tooltipWidth - $cornerSize, 0, $cornerSize, $cornerSize); // Top right

            imagecopy($image, $tooltipImage, 0, $imageHeight - $cornerSize, 0, $tooltipHeight - $cornerSize, $cornerSize, $cornerSize); // Bottom left
            imagecopy($image, $tooltipImage, $imageWidth - $cornerSize, $imageHeight - $cornerSize, $tooltipWidth - $cornerSize, $tooltipHeight - $cornerSize, $cornerSize, $cornerSize); // Bottom right

            $purple = imagecolorallocate($image, 16, 1, 16);

            imagefill($image, $cornerSize + 1, $cornerSize + 1, $purple);

            return $image;
        }

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         */
        protected function signature(Request $request, Player $player): Response {
            $image = self::getTooltipImage(430, 170);

            $fontMinecraftRegular = resource_path('fonts/Minecraft/1_Minecraft-Regular.otf');

            $rank     = $player->getRank(false);
            $rankName = $rank->getColor() . $rank->getCleanName();

            $bbox = ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, 16, 10, 9, '§aCharacter Information', true);

            $spacing  = 22;
            $start    = $bbox[0] + $spacing;
            $fontSize = 15;

            // Rank
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 10, $start, '§7Rank: ' . $rankName, true);

            // Level
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 10, $start + $spacing, '§7Level: §6' . number_format($player->getLevel()), true);

            $level     = $player->getLevel();
            $expNeeded = Leveling::getTotalExpToLevel($level + 1) - Leveling::getExperience($player);

            // Exp until next level
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 10, $start + 2 * $spacing, '§7Experience until next Level: §6' . number_format($expNeeded), true);

            // Achievement Points
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 10, $start + 3 * $spacing, '§7Achievement Points: §e' . number_format(Arr::get($player->getAchievementData(), 'standard.points.current', 0)), true);

            $quests          = new Collection($player->getArray('quests'));
            $questsCompleted = $quests->whereNotNull('completions')->map(static function ($quest) {
                return $quest['completions'];
            })->flatten()->count(); // Unfortunately, the number shown in-game might differ from the actual amount

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 10, $start + 4 * $spacing, '§7Quests Completed: §6' . number_format($questsCompleted), true);

            // Karma
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 10, $start + 5 * $spacing, '§7Karma: §d' . number_format($player->getInt('karma')), true);

            return response(Image::read($image)->encodeByExtension('png'))
                ->header('Content-Type', 'image/png')
                ->setCache([
                    'public'  => true,
                    'max_age' => 600
                ]);
        }
    }
