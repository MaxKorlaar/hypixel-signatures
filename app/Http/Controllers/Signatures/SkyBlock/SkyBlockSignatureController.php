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

    namespace App\Http\Controllers\Signatures\SkyBlock;

    use App\Exceptions\HypixelFetchException;
    use App\Exceptions\SkyBlockEmptyProfileException;
    use App\Http\Controllers\Signatures\BaseSignature;
    use App\Http\Controllers\Signatures\TooltipSignatureController;
    use App\Utilities\ColourHelper;
    use App\Utilities\SkyBlock\SkyBlockStatsDataParser;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class SkyBlockSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class SkyBlockSignatureController extends BaseSignature {
        protected ?string $profileId;

        public function render(Request $request, string $uuid, string $profileId = null): Response {
            $this->profileId = $profileId;

            return parent::render($request, $uuid);
        }

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         */
        protected function signature(Request $request, Player $player): Response {
            if ($this->profileId === null) {
                return self::generateErrorImage('Player does not have any SkyBlock profiles on their account, or they may have disabled API access for SkyBlock.');
            }

            try {
                $stats = SkyBlockStatsDataParser::getSkyBlockStats($player, $this->profileId);
            } catch (HypixelFetchException $exception) {
                return self::generateErrorImage('An error has occurred while trying to fetch this SkyBlock profile. Please try again later.');
            } catch (SkyBlockEmptyProfileException $e) {
                return self::generateErrorImage('This SkyBlock profile has no data. It may have been deleted.');
            }

            $image = TooltipSignatureController::getTooltipImage(325, 260);

            $fontMinecraftRegular = resource_path('fonts/Minecraft/1_Minecraft-Regular.otf');
            $unifont              = resource_path('fonts/Unifont/unifont-13.0.02.ttf');

            $calculatedStats = $stats->get('stats_with_sword') ?? $stats->get('stats');

            $bbox          = ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, 16, 10, 9, '§aYour SkyBlock Profile');
            $spacing       = 22;
            $start         = $bbox[0] + $spacing;
            $fontSize      = 15;
            $fontSizeGlyph = 13;

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 10, $start, "§c  ♥ Health §f{$calculatedStats['health']} HP", true);
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 10, $start + $spacing, "§a  ☼ Defense §f{$calculatedStats['defense']}", true);

            ColourHelper::minecraftStringToTTFText($image, $unifont, $fontSizeGlyph, 26, $start + $spacing * 2, '§c❁', true);
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 52, $start + $spacing * 2, "§cStrength §f{$calculatedStats['strength']}", true);

            ColourHelper::minecraftStringToTTFText($image, $unifont, $fontSizeGlyph, 26, $start + $spacing * 3, '§f✦', true);
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 52, $start + $spacing * 3, "§fSpeed §f{$calculatedStats['speed']}", true);

            ColourHelper::minecraftStringToTTFText($image, $unifont, $fontSizeGlyph, 26, $start + $spacing * 4, '§9☣', true);
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 52, $start + $spacing * 4, "§9Crit Chance §f{$calculatedStats['crit_chance']}%", true);

            $this->copyIcon('skull_blue', $image, 27, $start + $spacing * 5 + 2, 2.2);
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 52, $start + $spacing * 5, "§9Crit Damage §f{$calculatedStats['crit_damage']}%", true);

            ColourHelper::minecraftStringToTTFText($image, $unifont, $fontSizeGlyph, 26, $start + $spacing * 6, '§b✎', true);
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 52, $start + $spacing * 6, "§bIntelligence §f{$calculatedStats['intelligence']}", true);

            $this->copyIcon('alpha', $image, 27, $start + $spacing * 7 + 5, 2);
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 52, $start + $spacing * 7, "§3Sea Creature Chance §f{$calculatedStats['sea_creature_chance']}%", true);

            ColourHelper::minecraftStringToTTFText($image, $unifont, $fontSizeGlyph, 26, $start + $spacing * 8, '§b✯', true);
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 52, $start + $spacing * 8, "§bMagic Find §f{$calculatedStats['magic_find']}", true);

            $this->copyIcon('clubs', $image, 29, $start + $spacing * 9 + 4, 2);
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftRegular, $fontSize, 52, $start + $spacing * 9, "§dPet Luck §f{$calculatedStats['pet_luck']}", true);

            $this->addWatermark($image, $fontMinecraftRegular, 320, 255, 8);

            return Image::make($image)->response('png')->setCache([
                'public'  => true,
                'max_age' => 600
            ]);
        }

        /**
         * @param     $imageName
         * @param     $image
         * @param     $destX
         * @param     $destY
         * @param int $size
         */
        private function copyIcon($imageName, &$image, $destX, $destY, $size = 2): void {
            $icon  = imagecreatefrompng(resource_path("images/{$imageName}.png"));
            $iconX = imagesx($icon);
            $iconY = imagesy($icon);

            imagecopyresized($image, $icon, $destX, $destY, 0, 0, $iconX * $size, $iconY * $size, $iconX, $iconY);

            $icon  = imagecreatefrompng(resource_path("images/{$imageName}_shadow.png"));
            $iconX = imagesx($icon);
            $iconY = imagesy($icon);

            imagecopyresized($image, $icon, $destX + 2, $destY + 2, 0, 0, $iconX * $size, $iconY * $size, $iconX, $iconY);
        }


    }
