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

    namespace App\Utilities\MinecraftAvatar\ThreeD;

    /**
     * Class Polygon
     */
    class Polygon {
        private $_dots;
        private $_colour;
        private $_isProjected = false;
        private $_face = 'w';
        private $_faceDepth = 0;

        /**
         * @param $dots
         * @param $colour
         */
        public function __construct($dots, $colour) {
            $this->_dots   = $dots;
            $this->_colour = $colour;
            $coord_0       = $dots[0]->getOriginCoord();
            $coord_1       = $dots[1]->getOriginCoord();
            $coord_2       = $dots[2]->getOriginCoord();
            if ($coord_0['x'] == $coord_1['x'] && $coord_1['x'] == $coord_2['x']) {
                $this->_face      = 'x';
                $this->_faceDepth = $coord_0['x'];
            } elseif ($coord_0['y'] == $coord_1['y'] && $coord_1['y'] == $coord_2['y']) {
                $this->_face      = 'y';
                $this->_faceDepth = $coord_0['y'];
            } elseif ($coord_0['z'] == $coord_1['z'] && $coord_1['z'] == $coord_2['z']) {
                $this->_face      = 'z';
                $this->_faceDepth = $coord_0['z'];
            }
        }

        // never used

        /**
         * @param $ratio
         *
         * @return string
         */
        public function getSvgPolygon($ratio) {
            $points_2d = '';
            $r         = ($this->_colour >> 16) & 0xFF;
            $g         = ($this->_colour >> 8) & 0xFF;
            $b         = $this->_colour & 0xFF;
            $vR        = (127 - (($this->_colour & 0x7F000000) >> 24)) / 127;
            if ($vR == 0) {
                return '';
            }
            foreach ($this->_dots as $dot) {
                $coord     = $dot->getDestCoord();
                $points_2d .= $coord['x'] * $ratio . ',' . $coord['y'] * $ratio . ' ';
            }
            $comment = '';
            return $comment . '<polygon points="' . $points_2d . '" style="fill:rgba(' . $r . ',' . $g . ',' . $b . ',' . $vR . ')" />' . "\n";
        }

        // never used

        /**
         * @param $image
         * @param $minX
         * @param $minY
         * @param $ratio
         */
        public function addPngPolygon(&$image, $minX, $minY, $ratio) {
            $points_2d = [];
            $nb_points = 0;
            $r         = ($this->_colour >> 16) & 0xFF;
            $g         = ($this->_colour >> 8) & 0xFF;
            $b         = $this->_colour & 0xFF;
            $vR        = (127 - (($this->_colour & 0x7F000000) >> 24)) / 127;
            if ($vR == 0) {
                return;
            }
            $same_plan_x = true;
            $same_plan_y = true;
            foreach ($this->_dots as $dot) {
                $coord = $dot->getDestCoord();
                if (!isset($coord_x)) {
                    $coord_x = $coord['x'];
                }
                if (!isset($coord_y)) {
                    $coord_y = $coord['y'];
                }
                if ($coord_x != $coord['x']) {
                    $same_plan_x = false;
                }
                if ($coord_y != $coord['y']) {
                    $same_plan_y = false;
                }
                $points_2d[] = ($coord['x'] - $minX) * $ratio;
                $points_2d[] = ($coord['y'] - $minY) * $ratio;
                $nb_points++;
            }
            if (!($same_plan_x || $same_plan_y)) {
                $colour = imagecolorallocate($image, $r, $g, $b);
                imagefilledpolygon($image, $points_2d, $nb_points, $colour);
            }
        }

        /**
         * @return bool
         */
        public function isProjected() {
            return $this->_isProjected;
        }

        /**
         * @param $dx
         * @param $dy
         * @param $dz
         * @param $cos_alpha
         * @param $sin_alpha
         * @param $cos_omega
         * @param $sin_omega
         */
        public function preProject($dx, $dy, $dz, $cos_alpha, $sin_alpha, $cos_omega, $sin_omega) {
            foreach ($this->_dots as &$dot) {
                $dot->preProject($dx, $dy, $dz, $cos_alpha, $sin_alpha, $cos_omega, $sin_omega);
            }
        }

        /**
         * @return string
         */
        private function getFace() {
            return $this->_face;
        }

        /**
         * @return int
         */
        private function getFaceDepth() {
            if (!$this->_isProjected) {
                $this->project();
            }
            return $this->_faceDepth;
        }

        public function project() {
            foreach ($this->_dots as &$dot) {
                if (!$dot->isProjected()) {
                    $dot->project();
                }
            }
            unset($dot);
            $this->_isProjected = true;
        }
    }
