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
     * Class Point
     */
    class Point {
        private $_originCoord;
        private $_destCoord = [];
        private $_isProjected = false;
        private $_isPreProjected = false;

        /**
         * @param $originCoord
         */
        public function __construct($originCoord) {
            if (is_array($originCoord) && count($originCoord) == 3) {
                $this->_originCoord = [
                    'x' => (isset($originCoord['x']) ? $originCoord['x'] : 0),
                    'y' => (isset($originCoord['y']) ? $originCoord['y'] : 0),
                    'z' => (isset($originCoord['z']) ? $originCoord['z'] : 0)
                ];
            } else {
                $this->_originCoord = [
                    'x' => 0,
                    'y' => 0,
                    'z' => 0
                ];
            }
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
            if (!$this->_isPreProjected) {
                $x                       = $this->_originCoord['x'] - $dx;
                $y                       = $this->_originCoord['y'] - $dy;
                $z                       = $this->_originCoord['z'] - $dz;
                $this->_originCoord['x'] = $x * $cos_omega + $z * $sin_omega + $dx;
                $this->_originCoord['y'] = $x * $sin_alpha * $sin_omega + $y * $cos_alpha - $z * $sin_alpha * $cos_omega + $dy;
                $this->_originCoord['z'] = -$x * $cos_alpha * $sin_omega + $y * $sin_alpha + $z * $cos_alpha * $cos_omega + $dz;
                $this->_isPreProjected   = true;
            }
        }

        /**
         * @return array
         */
        public function getOriginCoord() {
            return $this->_originCoord;
        }

        /**
         * @return array
         */
        public function getDestCoord() {
            return $this->_destCoord;
        }

        /**
         * @return mixed
         */
        public function getDepth() {
            if (!$this->_isProjected) {
                $this->project();
            }
            return $this->_destCoord['z'];
        }

        public function project() {
            global $cos_alpha, $sin_alpha, $cos_omega, $sin_omega;
            global $minX, $maxX, $minY, $maxY;

            // 1, 0, 1, 0
            $x                     = $this->_originCoord['x'];
            $y                     = $this->_originCoord['y'];
            $z                     = $this->_originCoord['z'];
            $this->_destCoord['x'] = $x * $cos_omega + $z * $sin_omega;
            $this->_destCoord['y'] = $x * $sin_alpha * $sin_omega + $y * $cos_alpha - $z * $sin_alpha * $cos_omega;
            $this->_destCoord['z'] = -$x * $cos_alpha * $sin_omega + $y * $sin_alpha + $z * $cos_alpha * $cos_omega;
            $this->_isProjected    = true;
            $minX                  = min($minX, $this->_destCoord['x']);
            $maxX                  = max($maxX, $this->_destCoord['x']);
            $minY                  = min($minY, $this->_destCoord['y']);
            $maxY                  = max($maxY, $this->_destCoord['y']);
        }

        /**
         * @return bool
         */
        public function isProjected() {
            return $this->_isProjected;
        }
    }
