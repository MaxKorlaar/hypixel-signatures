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

    namespace App\Utilities;

    /**
     * Class ColourHelper
     * Provides functionality making it easier to convert "Minecraft-like" colour coded strings to text in PHP's gd image functions
     *
     * @author  github.com/Plancke
     * @author  Max Korlaar
     * @link    https://github.com/Plancke/hypixel-php
     * @package App\Utilities
     */
    class ColourHelper {

        /**
         * Add a Minecraft colour coded string to an image.
         *
         * @param      $image
         * @param      $font
         * @param      $fontSize
         * @param      $startX
         * @param      $startY
         * @param      $string
         *
         * @param bool $shadow
         *
         * @param bool $antiAlias
         */
        public static function minecraftStringToTTFText($image, $font, $fontSize, $startX, $startY, $string, $shadow = false, $antiAlias = true): array {
            $minecraftColours = [
                '0' => '#000000',
                '1' => '#0000AA',
                '2' => '#008000',
                '3' => '#00AAAA',
                '4' => '#AA0000',
                '5' => '#AA00AA',
                '6' => '#FFAA00',
                '7' => '#AAAAAA',
                '8' => '#555555',
                '9' => '#5555FF',
                'a' => '#3CE63C',
                'b' => '#55FFFF',
                'c' => '#FF5555',
                'd' => '#FF55FF',
                'e' => '#FFFF55',
                'f' => '#FFFFFF'
            ];

            if (!str_contains((string) $string, '§')) {
                $string = '§7' . $string;
            }

            $currentX = $startX;
            $currentY = $startY + 16;
            $bbox     = [];
            foreach (preg_split('/§/u', (string) $string, -1, PREG_SPLIT_NO_EMPTY) as $part) {
                $hexColour    = $minecraftColours[$part[0]] ?? $minecraftColours['7'];
                $rgb          = self::hexToRGB($hexColour);
                $colour       = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]) * ($antiAlias ? 1 : -1);
                $shadowColour = imagecolorallocate($image, $rgb[0] * 0.21, $rgb[1] * 0.21, $rgb[2] * 0.21) * ($antiAlias ? 1 : -1);

                $part = substr($part, 1);

                if ($shadow) {
                    imagettftext($image, $fontSize, 0, $currentX + 2, $currentY + 2, $shadowColour, $font, $part);
                }

                $bbox = imagettftext($image, $fontSize, 0, $currentX, $currentY, $colour, $font, $part);

                $currentX += ($bbox[4] - $bbox[0]);
            }
            return $bbox;
        }

        /**
         * Converts Hexadecimal colour code into RGB
         *
         * @param $hex
         */
        public static function hexToRGB($hex): array {
            $hex = str_replace('#', '', $hex);

            if (strlen($hex) === 3) {
                $r = hexdec($hex[0] . $hex[0]);
                $g = hexdec($hex[1] . $hex[1]);
                $b = hexdec($hex[2] . $hex[2]);
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }
            return [$r, $g, $b];
        }

    }
