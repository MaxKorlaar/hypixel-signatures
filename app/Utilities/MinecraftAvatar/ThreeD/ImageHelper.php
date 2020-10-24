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

    namespace App\Utilities\MinecraftAvatar\ThreeD;

    /**
     * Class ImageHelper
     */
    class ImageHelper {

        /**
         * Function converts a non true color image to
         * true color. This fixes the dark blue skins.
         *
         * Espects an image.
         * Returns a true color image.
         *
         * @param $img
         *
         * @return resource
         */
        public static function convertToTrueColor($img) {
            if (imageistruecolor($img)) {
                return $img;
            }

            $dst = self::createEmptyCanvas(imagesx($img), imagesy($img));

            imagecopy($dst, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
            imagedestroy($img);

            return $dst;
        }


        /**
         * Function creates a blank canvas
         * with transparancy with the size of the
         * given image.
         *
         * Espects canvas with and canvast height.
         * Returns a empty canvas.
         *
         * @param $w
         * @param $h
         *
         * @return resource
         */
        public static function createEmptyCanvas($w, $h) {
            $dst = imagecreatetruecolor($w, $h);
            imagesavealpha($dst, true);
            $trans_colour = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefill($dst, 0, 0, $trans_colour);
            $bg = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagecolortransparent($dst, $bg);
            return $dst;
        }
    }
