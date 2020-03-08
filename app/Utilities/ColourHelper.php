<?php

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
         * @param $image
         * @param $font
         * @param $fontSize
         * @param $startX
         * @param $startY
         * @param $string
         *
         * @return array
         */
        public static function minecraftStringToTTFText($image, $font, $fontSize, $startX, $startY, $string): array {
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

            if (strpos($string, '§') === false) {
                $string = '§7' . $string;
            }

            $currentX = $startX;
            $currentY = $startY + 16;
            $bbox     = [];
            foreach (preg_split('/§/u', $string, -1, PREG_SPLIT_NO_EMPTY) as $part) {
                $hexColour = $minecraftColours[$part[0]] ?? $minecraftColours['7'];
                $rgb       = self::hexToRGB($hexColour);
                $colour    = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);

                $part = substr($part, 1);

                $bbox     = imagettftext($image, $fontSize, 0, $currentX, $currentY, $colour, $font, $part);
                $currentX += ($bbox[4] - $bbox[0]);
            }
            return $bbox;
        }

        /**
         * Converts Hexadecimal colour code into RGB
         *
         * @param $hex
         *
         * @return array
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
