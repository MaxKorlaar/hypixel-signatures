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

    /**
     * Created by Max in 2020
     */

    namespace App\Utilities\Minecraft;

    use App\Utilities\ColourHelper;

    /**
     * Class Banner
     *
     * @package App\Utilities\Minecraft
     */
    class Banner {
        /**
         * @link https://minecraft.gamepedia.com/Dye#Colors
         * @var array|string[]
         */
        private array $dyeColors = [
            '#1D1D21',
            '#B02E26',
            '#5E7C16',
            '#835432',
            '#3C44AA',
            '#8932B8',
            '#169C9C',
            '#9D9D97',
            '#474F52',
            '#F38BAA',
            '#80C71F',
            '#FED83D',
            '#3AB3DA',
            '#C74EBD',
            '#F9801D',
            '#F9FFFE'
        ];

        private array $patternNames = [
            'b'   => 'base',
            'bs'  => 'stripe_bottom',
            'ts'  => 'stripe_top',
            'ls'  => 'stripe_left',
            'rs'  => 'stripe_right',
            'cs'  => 'stripe_center',
            'ms'  => 'stripe_middle',
            'drs' => 'stripe_downright',
            'dls' => 'stripe_downleft',
            'ss'  => 'small_stripes',
            'cr'  => 'cross',
            'sc'  => 'straight_cross',
            'ld'  => 'diagonal_left',
            'rud' => 'diagonal_right',
            'lud' => 'diagonal_up_left',
            'rd'  => 'diagonal_up_right',
            'vh'  => 'half_vertical',
            'vhr' => 'half_vertical_right',
            'hh'  => 'half_horizontal',
            'hhb' => 'half_horizontal_bottom',
            'bl'  => 'square_bottom_left',
            'br'  => 'square_bottom_right',
            'tl'  => 'square_top_left',
            'tr'  => 'square_top_right',
            'bt'  => 'triangle_bottom',
            'tt'  => 'triangle_top',
            'bts' => 'triangles_bottom',
            'tts' => 'triangles_top',
            'mc'  => 'circle',
            'mr'  => 'rhombus',
            'bo'  => 'border',
            'cbo' => 'curly_border',
            'bri' => 'brick',
            'gra' => 'gradient',
            'gru' => 'gradient_up',
            'cre' => 'creeper',
            'sku' => 'skull',
            'flo' => 'flower',
            'moj' => 'mojang',
            'glb' => 'globe',
        ];

        private array $properties;

        /**
         * BannerGenerator constructor.
         *
         * @param array $bannerProperties
         */
        public function __construct(array $bannerProperties) {
            $this->properties = $bannerProperties;
        }

        /**
         * @return false|resource
         */
        public function generate() {
            $bannerWidth  = 20;
            $bannerHeight = 40;

            $base = imagecreatetruecolor($bannerWidth, $bannerHeight);

            [$r, $g, $b] = ColourHelper::hexToRGB($this->dyeColors[$this->properties['Base']]);
            $baseColor = imagecolorallocate($base, $r, $g, $b);

            imagefill($base, 0, 0, $baseColor);

            foreach ($this->properties['Patterns'] as $pattern) {
                [$r, $g, $b] = ColourHelper::hexToRGB($this->dyeColors[$pattern['Color']]);

                $patternSourceImage = imagecreatefrompng(resource_path('images/banners/' . $this->patternNames[$pattern['Pattern']] . '.png'));

                $patternTargetImage = imagecreatetruecolor($bannerWidth, $bannerHeight);

                $trans_colour = imagecolorallocatealpha($patternTargetImage, 255, 255, 255, 127);
                imagefill($patternTargetImage, 0, 0, $trans_colour);
                $bg = imagecolorallocatealpha($patternTargetImage, 255, 255, 255, 127);
                imagecolortransparent($patternTargetImage, $bg);

                for ($x = 0; $x < $bannerWidth; $x++) {
                    for ($y = 0; $y < $bannerHeight; $y++) {
                        $colorIndex  = imagecolorat($patternSourceImage, $x + 1, $y + 1);
                        $indexColors = imagecolorsforindex($patternSourceImage, $colorIndex);

                        $alpha = $indexColors['alpha'];

                        $color = imagecolorallocatealpha($patternTargetImage, $r, $g, $b, $alpha);
                        imagesetpixel($patternTargetImage, $x, $y, $color);
                    }
                }

                imagecopy($base, $patternTargetImage, 0, 0, 0, 0, $bannerWidth, $bannerHeight);
                imagedestroy($patternSourceImage);
                imagedestroy($patternTargetImage);
            }

            return $base;
        }
    }
