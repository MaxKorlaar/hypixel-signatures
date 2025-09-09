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

    namespace App\Utilities\MinecraftAvatar;

    use Exception;
    use RuntimeException;

    /**
     * Create an animated GIF from multiple images
     *
     * @version   1.0
     * @link      https://github.com/Sybio/GifCreator
     * @author    Sybio (Clément Guillemain  / @Sybio01)
     * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
     * @copyright Clément Guillemain
     * @modified  by Max Korlaar
     */
    class GifCreator {
        /**
         * @var string The gif string source (old: this->GIF)
         */
        private $gif;

        /**
         * @var string Encoder version (old: this->VER)
         */
        // Static data
        private $version = 'GifCreator: Under development';

        /**
         * @var bool Check the image is build or not (old: this->IMG)
         */
        private $imgBuilt;

        /**
         * @var array Frames string sources (old: this->BUF)
         */
        private $frameSources;

        /**
         * @var int Gif loop (old: this->LOP)
         */
        private $loop;

        /**
         * @var int Gif dis (old: this->DIS)
         */
        private $dis;

        /**
         * @var int Gif color (old: this->COL)
         */
        private $colour;

        /**
         * @var array (old: this->ERR)
         */
        private $errors = [
            'ERR00' => 'Does not supported function for only one image.',
            'ERR01' => 'Source is not a GIF image.',
            'ERR02' => 'You have to give resource image variables, image URL or image binary sources in $frames array.',
            'ERR03' => 'Does not make animation from animated GIF source.',
        ];

        // Methods
        // ===================================================================================

        /**
         * Constructor
         */
        public function __construct() {
            $this->reset();
        }

        /**
         * Reset and clean the current object
         */
        public function reset() {
            $this->gif      = 'GIF89a'; // the GIF header
            $this->imgBuilt = false;
            $this->loop     = 0;
            $this->dis      = 2;
            $this->colour   = -1;
        }

        // Internals
        // ===================================================================================

        /**
         * Create the GIF string (old: GIFEncoder)
         *
         * @param array $frames    An array of frame: can be file paths, resource image variables, binary sources or image URLs
         * @param array $durations An array containing the duration of each frame
         * @param int   $loop      Number of GIF loops before stopping animation (Set 0 to get an infinite loop)
         *
         * @return string The GIF string source
         * @throws Exception
         */
        public function create($frames = [], $durations = [], $loop = 0) {
            if (!is_array($frames) && !is_array($durations)) {

                throw new Exception($this->version . ': ' . $this->errors['ERR00']);
            }

            $this->loop = ($loop > -1) ? $loop : 0;
            $this->dis  = 2;

            for ($i = 0, $iMax = count($frames); $i < $iMax; $i++) {

                if (is_resource($frames[$i])) { // Resource var

                    $resourceImg = $frames[$i];

                    ob_start();
                    imagegif($frames[$i]);
                    $this->frameSources[] = ob_get_clean();

                } elseif (is_string($frames[$i])) { // File path or URL or Binary source code

                    if (file_exists($frames[$i]) || filter_var($frames[$i], FILTER_VALIDATE_URL)) { // File path

                        $frames[$i] = file_get_contents($frames[$i]);
                    }

                    $resourceImg = imagecreatefromstring($frames[$i]);

                    ob_start();
                    imagegif($resourceImg);
                    $this->frameSources[] = ob_get_clean();

                } else { // Fail

                    throw new RuntimeException($this->version . ': ' . $this->errors['ERR02']);
                }

                if ($i === 0) {

                    $colour = imagecolortransparent($resourceImg);
                }

                if (!str_starts_with((string) $this->frameSources[$i], 'GIF87a') && !str_starts_with((string) $this->frameSources[$i], 'GIF89a')) {

                    throw new RuntimeException($this->version . ': ' . $i . ' ' . $this->errors['ERR01']);
                }

                for ($j = (13 + 3 * (2 << (ord($this->frameSources[$i][10]) & 0x07))), $k = true; $k; $j++) {

                    switch ($this->frameSources[$i][$j]) {

                        case '!':

                            if ((substr((string) $this->frameSources[$i], ($j + 3), 8)) === 'NETSCAPE') {

                                throw new RuntimeException($this->version . ': ' . $this->errors['ERR03'] . ' (' . ($i + 1) . ' source).');
                            }

                            break;

                        case ';':

                            $k = false;
                            break;
                    }
                }

                unset($resourceImg);
            }

            if (isset($colour)) {

                $this->colour = $colour;

            } else {

                $red          = $green = $blue = 0;
                $this->colour = ($red > -1 && $green > -1 && $blue > -1) ? ($red | ($green << 8) | ($blue << 16)) : -1;
            }

            $this->gifAddHeader();

            for ($i = 0, $iMax = count($this->frameSources); $i < $iMax; $i++) {

                $this->addGifFrames($i, $durations[$i]);
            }

            $this->gifAddFooter();

            return $this->gif;
        }

        /**
         * Add the header gif string in its source (old: GIFAddHeader)
         */
        public function gifAddHeader(): void {

            if ((ord($this->frameSources[0][10]) & 0x80) !== 0) {

                $cmap = 3 * (2 << (ord($this->frameSources[0][10]) & 0x07));

                $this->gif .= substr((string) $this->frameSources[0], 6, 7);
                $this->gif .= substr((string) $this->frameSources[0], 13, $cmap);
                $this->gif .= "!\377\13NETSCAPE2.0\3\1" . $this->encodeAsciiToChar($this->loop) . "\0";
            }
        }

        /**
         * Encode an ASCII char into a string char (old: GIFWord)
         * $param integer $char ASCII char
         *
         * @param $char
         */
        public function encodeAsciiToChar($char): string {
            return (chr($char & 0xFF) . chr(($char >> 8) & 0xFF));
        }

        /**
         * Add the frame sources to the GIF string (old: GIFAddFrames)
         *
         * @param int $i
         * @param int $d
         */
        public function addGifFrames($i, $d): void {
            $Locals_str = 13 + 3 * (2 << (ord($this->frameSources[$i][10]) & 0x07));

            $Locals_end = strlen((string) $this->frameSources[$i]) - $Locals_str - 1;
            $Locals_tmp = substr((string) $this->frameSources[$i], $Locals_str, $Locals_end);

            $Global_len = 2 << (ord($this->frameSources[0][10]) & 0x07);
            $Locals_len = 2 << (ord($this->frameSources[$i][10]) & 0x07);

            $Global_rgb = substr((string) $this->frameSources[0], 13, 3 * (2 << (ord($this->frameSources[0][10]) & 0x07)));
            $Locals_rgb = substr((string) $this->frameSources[$i], 13, 3 * (2 << (ord($this->frameSources[$i][10]) & 0x07)));

            $Locals_ext = "!\xF9\x04" . chr(($this->dis << 2) + 0) . chr(($d >> 0) & 0xFF) . chr(($d >> 8) & 0xFF) . "\x0\x0";

            if ($this->colour > -1 && ord($this->frameSources[$i][10]) & 0x80) {

                for ($j = 0; $j < (2 << (ord($this->frameSources[$i][10]) & 0x07)); $j++) {

                    if (ord($Locals_rgb[3 * $j]) === ($this->colour >> 16 & 0xFF) &&
                        ord($Locals_rgb[3 * $j + 1]) === ($this->colour >> 8 & 0xFF) &&
                        ord($Locals_rgb[3 * $j + 2]) === ($this->colour >> 0 & 0xFF)
                    ) {
                        $Locals_ext = "!\xF9\x04" . chr(($this->dis << 2) + 1) . chr(($d >> 0) & 0xFF) . chr(($d >> 8) & 0xFF) . chr($j) . "\x0";
                        break;
                    }
                }
            }

            switch ($Locals_tmp[0]) {

                case '!':

                    $Locals_img = substr($Locals_tmp, 8, 10);
                    $Locals_tmp = substr($Locals_tmp, 18);

                    break;

                case ',':

                    $Locals_img = substr($Locals_tmp, 0, 10);
                    $Locals_tmp = substr($Locals_tmp, 10);

                    break;
            }

            if (ord($this->frameSources[$i][10]) & 0x80 && $this->imgBuilt) {

                if ($Global_len === $Locals_len) {

                    if ($this->gifBlockCompare($Global_rgb, $Locals_rgb, $Global_len)) {

                        $this->gif .= $Locals_ext . $Locals_img . $Locals_tmp;

                    } else {

                        $byte          = ord($Locals_img[9]);
                        $byte          |= 0x80;
                        $byte          &= 0xF8;
                        $byte          |= (ord($this->frameSources[0][10]) & 0x07);
                        $Locals_img[9] = chr($byte);
                        $this->gif     .= $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp;
                    }

                } else {

                    $byte          = ord($Locals_img[9]);
                    $byte          |= 0x80;
                    $byte          &= 0xF8;
                    $byte          |= (ord($this->frameSources[$i][10]) & 0x07);
                    $Locals_img[9] = chr($byte);
                    $this->gif     .= $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp;
                }

            } else {

                $this->gif .= $Locals_ext . $Locals_img . $Locals_tmp;
            }

            $this->imgBuilt = true;
        }

        /**
         * Compare two block and return the version (old: GIFBlockCompare)
         *
         * @param string $globalBlock
         * @param string $localBlock
         * @param int    $length
         *
         * @return int
         */
        public function gifBlockCompare($globalBlock, $localBlock, $length) {
            for ($i = 0; $i < $length; $i++) {

                if ($globalBlock[3 * $i] != $localBlock[3 * $i] ||
                    $globalBlock[3 * $i + 1] != $localBlock[3 * $i + 1] ||
                    $globalBlock[3 * $i + 2] != $localBlock[3 * $i + 2]
                ) {

                    return 0;
                }
            }

            return 1;
        }

        /**
         * Add the gif string footer char (old: GIFAddFooter)
         */
        public function gifAddFooter() {
            $this->gif .= ';';
        }

        // Getter / Setter
        // ===================================================================================

        /**
         * Get the final GIF image string (old: GetAnimation)
         *
         * @return string
         */
        public function getGif() {
            return $this->gif;
        }
    }
