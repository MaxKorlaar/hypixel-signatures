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

    namespace App\Utilities\MinecraftAvatar;

    use Exception;
    use Illuminate\Support\Facades\Log;
    use Psr\SimpleCache\InvalidArgumentException;

    /****** MINECRAFT 3D Skin Generator *****
     * The contents of this project were first developed by Pierre Gros on 17th April 2012.
     * It has once been modified by Carlos Ferreira (http://www.carlosferreira.me) on 31st May 2014.
     * Translations done by Carlos Ferreira.
     * Later adapted by Gijs "Gyzie" Oortgiese (http://www.gijsoortgiese.com/). Started on the 6st of July 2014.
     * Fixing various issues.
     * Later modified by Max Korlaar (24th of January 2015):
     * - GET requests removed
     * - Skins are taken from the minecraftAvatar class, instead of directly from Mojang.
     * This reduces the need of redownloading skins.
     * - Some typos and other small errors.
     * - Removed script duration functions, those are not needed.
     * - Changed array syntax.
     */

    /* Start Global variables
     * These variabals are shared over multiple classes
     */

    // Cosine and Sine values
    $cos_alpha = null;
    $sin_alpha = null;
    $cos_omega = null;
    $sin_omega = null;

    $minX = null;
    $maxX = null;
    $minY = null;
    $maxY = null;
    /* End Global variable */


    /**
     * Class render3DPlayer
     */
    class render3DPlayer {
        public $fetchError = null;
        private $playerName = null;
        private $playerSkin = false;
        private $isNewSkinType = false;
        private $hd_ratio = 1;
        private $vR = null;
        private $hR = null;
        private $hrh = null;
        private $vrll = null;
        private $vrrl = null;
        private $vrla = null;
        private $vrra = null;
        private $head_only = null;
        private $display_hair = null;
        private $format = null;
        // Rotation variables in radians (3D Rendering)
        private $ratio = null; // Vertical rotation on the X axis.
        private $aa = null; // Horizontal rotation on the Y axis.
        private $layers = null; // Head, Helmet, Torso, Arms, Legs
        private $alpha = null;
        private $omega = null;
        private $members_angles = [];
        private $visible_faces_format = null;
        private $visible_faces = null;
        private $all_faces = null;
        private $front_faces = null;
        private $back_faces = null;
        private $cube_points = null;
        private $polygons = null;

        /**
         * @param      $user
         * @param      $vr
         * @param      $hr
         * @param      $hrh
         * @param      $vrll
         * @param      $vrrl
         * @param      $vrla
         * @param      $vrra
         * @param      $displayHair
         * @param      $headOnly
         * @param      $format
         * @param      $ratio
         * @param      $layers
         * @param bool $aa
         */
        public function __construct($user, $vr, $hr, $hrh, $vrll, $vrrl, $vrla, $vrra, $displayHair, $headOnly, $format, $ratio, $layers, $aa = true) {
            $this->playerName   = $user;
            $this->vR           = $vr;
            $this->hR           = $hr;
            $this->hrh          = $hrh;
            $this->vrll         = $vrll;
            $this->vrrl         = $vrrl;
            $this->vrla         = $vrla;
            $this->vrra         = $vrra;
            $this->head_only    = $headOnly;
            $this->display_hair = $displayHair;
            $this->format       = $format;
            $this->ratio        = $ratio;
            $this->aa           = $aa;
            $this->layers       = $layers;
        }

        /** Function renders the 3d image
         *
         * @return resource|string
         * @throws InvalidArgumentException
         */
        public function get3DRender() {
            $this->getPlayerSkin(); // Download and check the player skin

            $this->hd_ratio = imagesx($this->playerSkin) / 64; // Set HD ratio to 2 if the skin is 128x64. Check via width, not height because of new skin type.

            // check if new skin type. If both sides are equaly long: new skin type
            if (imagesx($this->playerSkin) === imagesy($this->playerSkin)) {
                $this->isNewSkinType = true;
            }

            $this->playerSkin = ImageHelper::convertToTrueColor($this->playerSkin); // Convert the image to true color if not a true color image
            $this->makeBackgroundTransparent(); // make background transparent (fix for weird rendering skins)

            // Quick fix for 1.8:
            // Copy the extra layers ontop of the base layers
            if ($this->layers) {
                $this->fixNewSkinTypeLayers();
            }

            $this->calculateAngles();
            $this->facesDetermination();
            $this->generatePolygons();
            $this->memberRotation();
            $this->createProjectionPlan();
            return $this->displayImage('return');

        }

        /* Function gets the player skin URL via the Mojang service by UUID
         *
         * Espects an UUID.
         * Returns player skin texure link, false on failure
         */

        /**
         * @return bool
         */
        private function getPlayerSkin(): bool {
            if (trim($this->playerName) === '') {
                throw new \InvalidArgumentException('Playername or UUID is empty');
            }

            $MCavatar = new MCavatar();
            $skinURL  = $MCavatar->getSkinFromCache($this->playerName);

            $this->fetchError = $MCavatar->fetchError;

            Log::debug('Getting skin from existing URL: ' . $skinURL);

            try {
                $this->playerSkin = pathinfo($skinURL, PATHINFO_EXTENSION) === 'png' ? imagecreatefrompng($skinURL) : imagecreatefromwebp($skinURL);
            } catch (Exception $exception) {
                Log::warning('Image resource could not be created based on skin URL', [$skinURL, pathinfo($skinURL, PATHINFO_EXTENSION)]);
                report($exception);
            }

            Log::debug('Skin URL: ' . $skinURL);

            if (!$this->playerSkin) {
                // Player skin does not exist
                Log::debug('Something went wrong while creating an image resource from the image path');
                $this->playerSkin = imagecreatefrompng($MCavatar->getFallbackUrl());
                return false;
            }

            if (imagesy($this->playerSkin) % 32 !== 0) {
                // Bad ratio created
                Log::debug('Ratio incorrect');
                $this->fetchError = true;
                $this->playerSkin = imagecreatefrompng($MCavatar->getFallbackUrl());
                return false;
            }

            return true;
        }

        private function makeBackgroundTransparent(): void {
            // check if the corner box is one solid color
            $tempValue  = null;
            $needRemove = true;

            for ($iH = 0; $iH < 8; $iH++) {
                for ($iV = 0; $iV < 8; $iV++) {
                    $pixelColor = imagecolorat($this->playerSkin, $iH, $iV);

                    $indexColor = imagecolorsforindex($this->playerSkin, $pixelColor);
                    if ($indexColor['alpha'] > 120) {
                        // the image contains transparancy, noting to do
                        $needRemove = false;
                    }

                    if ($tempValue === null) {
                        $tempValue = $pixelColor;
                    } elseif ($tempValue !== $pixelColor) {
                        // Cannot determine a background color, file is probably fine
                        $needRemove = false;
                    }
                }
            }

            $imgX = imagesx($this->playerSkin);
            $imgY = imagesy($this->playerSkin);

            $dst = ImageHelper::createEmptyCanvas($imgX, $imgY);

            imagesavealpha($this->playerSkin, false);

            if ($needRemove) {
                // the entire block is one solid color. Use this color to clear the background.
                $r = ($tempValue >> 16) & 0xFF;
                $g = ($tempValue >> 8) & 0xFF;
                $b = $tempValue & 0xFF;

                //imagealphablending($dst, true);
                $transparant = imagecolorallocate($this->playerSkin, $r, $g, $b);
                imagecolortransparent($this->playerSkin, $transparant);

                // create fill
                $color = imagecolorallocate($dst, $r, $g, $b);
            } else {
                // create fill
                $color = imagecolorallocate($dst, 0, 0, 0);
            }

            // fill the areas that should not be transparant
            $positionMultiply = $imgX / 64;

            // head
            imagefilledrectangle($dst, 8 * $positionMultiply, 0 * $positionMultiply, 23 * $positionMultiply, 7 * $positionMultiply, $color);
            imagefilledrectangle($dst, 0 * $positionMultiply, 8 * $positionMultiply, 31 * $positionMultiply, 15 * $positionMultiply, $color);

            // right leg, body, right arm
            imagefilledrectangle($dst, 4 * $positionMultiply, 16 * $positionMultiply, 11 * $positionMultiply, 19 * $positionMultiply, $color);
            imagefilledrectangle($dst, 20 * $positionMultiply, 16 * $positionMultiply, 35 * $positionMultiply, 19 * $positionMultiply, $color);
            imagefilledrectangle($dst, 44 * $positionMultiply, 16 * $positionMultiply, 51 * $positionMultiply, 19 * $positionMultiply, $color);
            imagefilledrectangle($dst, 0 * $positionMultiply, 20 * $positionMultiply, 54 * $positionMultiply, 31 * $positionMultiply, $color);

            // left leg, left arm
            imagefilledrectangle($dst, 20 * $positionMultiply, 48 * $positionMultiply, 27 * $positionMultiply, 51 * $positionMultiply, $color);
            imagefilledrectangle($dst, 36 * $positionMultiply, 48 * $positionMultiply, 43 * $positionMultiply, 51 * $positionMultiply, $color);
            imagefilledrectangle($dst, 16 * $positionMultiply, 52 * $positionMultiply, 47 * $positionMultiply, 63 * $positionMultiply, $color);

            imagecopy($dst, $this->playerSkin, 0, 0, 0, 0, $imgX, $imgY);

            $this->playerSkin = $dst;
        }

        private function fixNewSkinTypeLayers(): void {
            if (!$this->isNewSkinType) {
                return;
            }

            imagecopy($this->playerSkin, $this->playerSkin, 0, 16, 0, 32, 56, 16); // RL2, BODY2, RA2
            imagecopy($this->playerSkin, $this->playerSkin, 16, 48, 0, 48, 16, 16); // LL2
            imagecopy($this->playerSkin, $this->playerSkin, 32, 48, 48, 48, 16, 16); // LA2
        }

        private function calculateAngles(): void {
            global $cos_alpha, $sin_alpha, $cos_omega, $sin_omega;
            global $minX, $maxX, $minY, $maxY;

            // Rotation variables in radians (3D Rendering)
            $this->alpha = deg2rad($this->vR); // Vertical rotation on the X axis.
            $this->omega = deg2rad($this->hR); // Horizontal rotation on the Y axis.

            // Cosine and Sine values
            $cos_alpha = cos($this->alpha);
            $sin_alpha = sin($this->alpha);
            $cos_omega = cos($this->omega);
            $sin_omega = sin($this->omega);

            $this->members_angles['torso'] = [
                'cos_alpha' => cos(0),
                'sin_alpha' => sin(0),
                'cos_omega' => cos(0),
                'sin_omega' => sin(0)
            ];

            $alpha_head                   = 0;
            $omega_head                   = deg2rad($this->hrh);
            $this->members_angles['head'] = $this->members_angles['helmet'] = [ // Head and helmet get the same calculations
                                                                                'cos_alpha' => cos($alpha_head),
                                                                                'sin_alpha' => sin($alpha_head),
                                                                                'cos_omega' => cos($omega_head),
                                                                                'sin_omega' => sin($omega_head)
            ];

            $alpha_right_arm                  = deg2rad($this->vrra);
            $omega_right_arm                  = 0;
            $this->members_angles['rightArm'] = [
                'cos_alpha' => cos($alpha_right_arm),
                'sin_alpha' => sin($alpha_right_arm),
                'cos_omega' => cos($omega_right_arm),
                'sin_omega' => sin($omega_right_arm)
            ];

            $alpha_left_arm                  = deg2rad($this->vrla);
            $omega_left_arm                  = 0;
            $this->members_angles['leftArm'] = [
                'cos_alpha' => cos($alpha_left_arm),
                'sin_alpha' => sin($alpha_left_arm),
                'cos_omega' => cos($omega_left_arm),
                'sin_omega' => sin($omega_left_arm)
            ];

            $alpha_right_leg                  = deg2rad($this->vrrl);
            $omega_right_leg                  = 0;
            $this->members_angles['rightLeg'] = [
                'cos_alpha' => cos($alpha_right_leg),
                'sin_alpha' => sin($alpha_right_leg),
                'cos_omega' => cos($omega_right_leg),
                'sin_omega' => sin($omega_right_leg)
            ];

            $alpha_left_leg                  = deg2rad($this->vrll);
            $omega_left_leg                  = 0;
            $this->members_angles['leftLeg'] = [
                'cos_alpha' => cos($alpha_left_leg),
                'sin_alpha' => sin($alpha_left_leg),
                'cos_omega' => cos($omega_left_leg),
                'sin_omega' => sin($omega_left_leg)
            ];
            $minX                            = 0;
            $maxX                            = 0;
            $minY                            = 0;
            $maxY                            = 0;
        }

        private function facesDetermination(): void {
            $this->visible_faces_format = [
                'front' => [],
                'back'  => []
            ];

            $this->visible_faces = [
                'head'     => $this->visible_faces_format,
                'torso'    => $this->visible_faces_format,
                'rightArm' => $this->visible_faces_format,
                'leftArm'  => $this->visible_faces_format,
                'rightLeg' => $this->visible_faces_format,
                'leftLeg'  => $this->visible_faces_format
            ];

            $this->all_faces = [
                'back',
                'right',
                'top',
                'front',
                'left',
                'bottom'
            ];

            // Loop each preProject and Project then calculate the visible faces for each - also display
            foreach ($this->visible_faces as $k => &$v) {
                unset($cube_max_depth_faces, $this->cube_points);

                $this->setCubePoints();

                foreach ($this->cube_points as $cube_point) {
                    $cube_point[0]->preProject(0, 0, 0,
                        $this->members_angles[$k]['cos_alpha'],
                        $this->members_angles[$k]['sin_alpha'],
                        $this->members_angles[$k]['cos_omega'],
                        $this->members_angles[$k]['sin_omega']);
                    $cube_point[0]->project();

                    if (!isset($cube_max_depth_faces)) {
                        $cube_max_depth_faces = $cube_point;
                    } elseif ($cube_max_depth_faces[0]->getDepth() > $cube_point[0]->getDepth()) {
                        $cube_max_depth_faces = $cube_point;
                    }
                }

                $v['back']  = $cube_max_depth_faces[1];
                $v['front'] = array_diff($this->all_faces, $v['back']);
            }

            $this->setCubePoints();

            unset($cube_max_depth_faces);
            foreach ($this->cube_points as $cube_point) {
                $cube_point[0]->project();

                if (!isset($cube_max_depth_faces)) {
                    $cube_max_depth_faces = $cube_point;
                } elseif ($cube_max_depth_faces[0]->getDepth() > $cube_point[0]->getDepth()) {
                    $cube_max_depth_faces = $cube_point;
                }

                $this->back_faces  = $cube_max_depth_faces[1];
                $this->front_faces = array_diff($this->all_faces, $this->back_faces);
            }
        }

        private function setCubePoints() {
            $this->cube_points   = [];
            $this->cube_points[] = [
                new Point([
                    'x' => 0,
                    'y' => 0,
                    'z' => 0
                ]), [
                    'back',
                    'right',
                    'top'
                ]]; // 0

            $this->cube_points[] = [
                new Point([
                    'x' => 0,
                    'y' => 0,
                    'z' => 1
                ]), [
                    'front',
                    'right',
                    'top'
                ]]; // 1

            $this->cube_points[] = [
                new Point([
                    'x' => 0,
                    'y' => 1,
                    'z' => 0
                ]), [
                    'back',
                    'right',
                    'bottom'
                ]]; // 2

            $this->cube_points[] = [
                new Point([
                    'x' => 0,
                    'y' => 1,
                    'z' => 1
                ]), [
                    'front',
                    'right',
                    'bottom'
                ]]; // 3

            $this->cube_points[] = [
                new Point([
                    'x' => 1,
                    'y' => 0,
                    'z' => 0
                ]), [
                    'back',
                    'left',
                    'top'
                ]]; // 4

            $this->cube_points[] = [
                new Point([
                    'x' => 1,
                    'y' => 0,
                    'z' => 1
                ]), [
                    'front',
                    'left',
                    'top'
                ]]; // 5

            $this->cube_points[] = [
                new Point([
                    'x' => 1,
                    'y' => 1,
                    'z' => 0
                ]), [
                    'back',
                    'left',
                    'bottom'
                ]]; // 6

            $this->cube_points[] = [
                new Point([
                    'x' => 1,
                    'y' => 1,
                    'z' => 1
                ]), [
                    'front',
                    'left',
                    'bottom'
                ]]; // 7
        }

        private function generatePolygons() {
            $this->polygons   = [];
            $cube_faces_array = ['front'  => [],
                                 'back'   => [],
                                 'top'    => [],
                                 'bottom' => [],
                                 'right'  => [],
                                 'left'   => []
            ];

            $this->polygons = ['helmet'   => $cube_faces_array,
                               'head'     => $cube_faces_array,
                               'torso'    => $cube_faces_array,
                               'rightArm' => $cube_faces_array,
                               'leftArm'  => $cube_faces_array,
                               'rightLeg' => $cube_faces_array,
                               'leftLeg'  => $cube_faces_array
            ];

            $hd_ratio = $this->hd_ratio;
            $img_png  = $this->playerSkin;

            // HEAD
            for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                for ($j = 0; $j < 9 * $hd_ratio; $j++) {
                    if (!isset($volume_points[$i][$j][-2 * $hd_ratio])) {
                        $volume_points[$i][$j][-2 * $hd_ratio] = new Point([
                            'x' => $i,
                            'y' => $j,
                            'z' => -2 * $hd_ratio
                        ]);
                    }
                    if (!isset($volume_points[$i][$j][6 * $hd_ratio])) {
                        $volume_points[$i][$j][6 * $hd_ratio] = new Point([
                            'x' => $i,
                            'y' => $j,
                            'z' => 6 * $hd_ratio
                        ]);
                    }
                }
            }
            for ($j = 0; $j < 9 * $hd_ratio; $j++) {
                for ($k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++) {
                    if (!isset($volume_points[0][$j][$k])) {
                        $volume_points[0][$j][$k] = new Point([
                            'x' => 0,
                            'y' => $j,
                            'z' => $k
                        ]);
                    }
                    if (!isset($volume_points[8 * $hd_ratio][$j][$k])) {
                        $volume_points[8 * $hd_ratio][$j][$k] = new Point([
                            'x' => 8 * $hd_ratio,
                            'y' => $j,
                            'z' => $k
                        ]);
                    }
                }
            }
            for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                for ($k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++) {
                    if (!isset($volume_points[$i][0][$k])) {
                        $volume_points[$i][0][$k] = new Point([
                            'x' => $i,
                            'y' => 0,
                            'z' => $k
                        ]);
                    }
                    if (!isset($volume_points[$i][8 * $hd_ratio][$k])) {
                        $volume_points[$i][8 * $hd_ratio][$k] = new Point([
                            'x' => $i,
                            'y' => 8 * $hd_ratio,
                            'z' => $k
                        ]);
                    }
                }
            }
            for ($i = 0; $i < 8 * $hd_ratio; $i++) {
                for ($j = 0; $j < 8 * $hd_ratio; $j++) {
                    $this->polygons['head']['back'][]  = new Polygon([
                        $volume_points[$i][$j][-2 * $hd_ratio],
                        $volume_points[$i + 1][$j][-2 * $hd_ratio],
                        $volume_points[$i + 1][$j + 1][-2 * $hd_ratio],
                        $volume_points[$i][$j + 1][-2 * $hd_ratio]
                    ], imagecolorat($img_png, (32 * $hd_ratio - 1) - $i, 8 * $hd_ratio + $j));
                    $this->polygons['head']['front'][] = new Polygon([
                        $volume_points[$i][$j][6 * $hd_ratio],
                        $volume_points[$i + 1][$j][6 * $hd_ratio],
                        $volume_points[$i + 1][$j + 1][6 * $hd_ratio],
                        $volume_points[$i][$j + 1][6 * $hd_ratio]
                    ], imagecolorat($img_png, 8 * $hd_ratio + $i, 8 * $hd_ratio + $j));
                }
            }
            for ($j = 0; $j < 8 * $hd_ratio; $j++) {
                for ($k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++) {
                    $this->polygons['head']['right'][] = new Polygon([
                        $volume_points[0][$j][$k],
                        $volume_points[0][$j][$k + 1],
                        $volume_points[0][$j + 1][$k + 1],
                        $volume_points[0][$j + 1][$k]
                    ], imagecolorat($img_png, $k + 2 * $hd_ratio, 8 * $hd_ratio + $j));
                    $this->polygons['head']['left'][]  = new Polygon([
                        $volume_points[8 * $hd_ratio][$j][$k],
                        $volume_points[8 * $hd_ratio][$j][$k + 1],
                        $volume_points[8 * $hd_ratio][$j + 1][$k + 1],
                        $volume_points[8 * $hd_ratio][$j + 1][$k]
                    ], imagecolorat($img_png, (24 * $hd_ratio - 1) - $k - 2 * $hd_ratio, 8 * $hd_ratio + $j));
                }
            }
            for ($i = 0; $i < 8 * $hd_ratio; $i++) {
                for ($k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++) {
                    $this->polygons['head']['top'][]    = new Polygon([
                        $volume_points[$i][0][$k],
                        $volume_points[$i + 1][0][$k],
                        $volume_points[$i + 1][0][$k + 1],
                        $volume_points[$i][0][$k + 1]
                    ], imagecolorat($img_png, 8 * $hd_ratio + $i, $k + 2 * $hd_ratio));
                    $this->polygons['head']['bottom'][] = new Polygon([
                        $volume_points[$i][8 * $hd_ratio][$k],
                        $volume_points[$i + 1][8 * $hd_ratio][$k],
                        $volume_points[$i + 1][8 * $hd_ratio][$k + 1],
                        $volume_points[$i][8 * $hd_ratio][$k + 1]
                    ], imagecolorat($img_png, 16 * $hd_ratio + $i, 2 * $hd_ratio + $k));
                }
            }
            if ($this->display_hair) {
                // HELMET/HAIR
                $volume_points = [];
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 9 * $hd_ratio; $j++) {
                        if (!isset($volume_points[$i][$j][-2 * $hd_ratio])) {
                            $volume_points[$i][$j][-2 * $hd_ratio] = new Point([
                                'x' => $i * 9 / 8 - 0.5 * $hd_ratio,
                                'y' => $j * 9 / 8 - 0.5 * $hd_ratio,
                                'z' => -2.5 * $hd_ratio
                            ]);
                        }
                        if (!isset($volume_points[$i][$j][6 * $hd_ratio])) {
                            $volume_points[$i][$j][6 * $hd_ratio] = new Point([
                                'x' => $i * 9 / 8 - 0.5 * $hd_ratio,
                                'y' => $j * 9 / 8 - 0.5 * $hd_ratio,
                                'z' => 6.5 * $hd_ratio
                            ]);
                        }
                    }
                }
                for ($j = 0; $j < 9 * $hd_ratio; $j++) {
                    for ($k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++) {
                        if (!isset($volume_points[0][$j][$k])) {
                            $volume_points[0][$j][$k] = new Point([
                                'x' => -0.5 * $hd_ratio,
                                'y' => $j * 9 / 8 - 0.5 * $hd_ratio,
                                'z' => $k * 9 / 8 - 0.5 * $hd_ratio
                            ]);
                        }
                        if (!isset($volume_points[8 * $hd_ratio][$j][$k])) {
                            $volume_points[8 * $hd_ratio][$j][$k] = new Point([
                                'x' => 8.5 * $hd_ratio,
                                'y' => $j * 9 / 8 - 0.5 * $hd_ratio,
                                'z' => $k * 9 / 8 - 0.5 * $hd_ratio
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++) {
                        if (!isset($volume_points[$i][0][$k])) {
                            $volume_points[$i][0][$k] = new Point([
                                'x' => $i * 9 / 8 - 0.5 * $hd_ratio,
                                'y' => -0.5 * $hd_ratio,
                                'z' => $k * 9 / 8 - 0.5 * $hd_ratio
                            ]);
                        }
                        if (!isset($volume_points[$i][8 * $hd_ratio][$k])) {
                            $volume_points[$i][8 * $hd_ratio][$k] = new Point([
                                'x' => $i * 9 / 8 - 0.5 * $hd_ratio,
                                'y' => 8.5 * $hd_ratio,
                                'z' => $k * 9 / 8 - 0.5 * $hd_ratio
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 8 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 8 * $hd_ratio; $j++) {
                        $this->polygons['helmet']['back'][]  = new Polygon([
                            $volume_points[$i][$j][-2 * $hd_ratio],
                            $volume_points[$i + 1][$j][-2 * $hd_ratio],
                            $volume_points[$i + 1][$j + 1][-2 * $hd_ratio],
                            $volume_points[$i][$j + 1][-2 * $hd_ratio]
                        ], imagecolorat($img_png, 32 * $hd_ratio + (32 * $hd_ratio - 1) - $i, 8 * $hd_ratio + $j));
                        $this->polygons['helmet']['front'][] = new Polygon([
                            $volume_points[$i][$j][6 * $hd_ratio],
                            $volume_points[$i + 1][$j][6 * $hd_ratio],
                            $volume_points[$i + 1][$j + 1][6 * $hd_ratio],
                            $volume_points[$i][$j + 1][6 * $hd_ratio]
                        ], imagecolorat($img_png, 32 * $hd_ratio + 8 * $hd_ratio + $i, 8 * $hd_ratio + $j));
                    }
                }
                for ($j = 0; $j < 8 * $hd_ratio; $j++) {
                    for ($k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++) {
                        $this->polygons['helmet']['right'][] = new Polygon([
                            $volume_points[0][$j][$k],
                            $volume_points[0][$j][$k + 1],
                            $volume_points[0][$j + 1][$k + 1],
                            $volume_points[0][$j + 1][$k]
                        ], imagecolorat($img_png, 32 * $hd_ratio + $k + 2 * $hd_ratio, 8 * $hd_ratio + $j));
                        $this->polygons['helmet']['left'][]  = new Polygon([
                            $volume_points[8 * $hd_ratio][$j][$k],
                            $volume_points[8 * $hd_ratio][$j][$k + 1],
                            $volume_points[8 * $hd_ratio][$j + 1][$k + 1],
                            $volume_points[8 * $hd_ratio][$j + 1][$k]
                        ], imagecolorat($img_png, 32 * $hd_ratio + (24 * $hd_ratio - 1) - $k - 2 * $hd_ratio, 8 * $hd_ratio + $j));
                    }
                }
                for ($i = 0; $i < 8 * $hd_ratio; $i++) {
                    for ($k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++) {
                        $this->polygons['helmet']['top'][]    = new Polygon([
                            $volume_points[$i][0][$k],
                            $volume_points[$i + 1][0][$k],
                            $volume_points[$i + 1][0][$k + 1],
                            $volume_points[$i][0][$k + 1]
                        ], imagecolorat($img_png, 32 * $hd_ratio + 8 * $hd_ratio + $i, $k + 2 * $hd_ratio));
                        $this->polygons['helmet']['bottom'][] = new Polygon([
                            $volume_points[$i][8 * $hd_ratio][$k],
                            $volume_points[$i + 1][8 * $hd_ratio][$k],
                            $volume_points[$i + 1][8 * $hd_ratio][$k + 1],
                            $volume_points[$i][8 * $hd_ratio][$k + 1]
                        ], imagecolorat($img_png, 32 * $hd_ratio + 16 * $hd_ratio + $i, 2 * $hd_ratio + $k));
                    }
                }
            }
            if (!$this->head_only) {
                // TORSO
                $volume_points = [];
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                        if (!isset($volume_points[$i][$j][0])) {
                            $volume_points[$i][$j][0] = new Point([
                                'x' => $i,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => 0
                            ]);
                        }
                        if (!isset($volume_points[$i][$j][4 * $hd_ratio])) {
                            $volume_points[$i][$j][4 * $hd_ratio] = new Point([
                                'x' => $i,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => 4 * $hd_ratio
                            ]);
                        }
                    }
                }
                for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[0][$j][$k])) {
                            $volume_points[0][$j][$k] = new Point([
                                'x' => 0,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[8 * $hd_ratio][$j][$k])) {
                            $volume_points[8 * $hd_ratio][$j][$k] = new Point([
                                'x' => 8 * $hd_ratio,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[$i][0][$k])) {
                            $volume_points[$i][0][$k] = new Point([
                                'x' => $i,
                                'y' => 0 + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[$i][12 * $hd_ratio][$k])) {
                            $volume_points[$i][12 * $hd_ratio][$k] = new Point([
                                'x' => $i,
                                'y' => 12 * $hd_ratio + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 8 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                        $this->polygons['torso']['back'][]  = new Polygon([
                            $volume_points[$i][$j][0],
                            $volume_points[$i + 1][$j][0],
                            $volume_points[$i + 1][$j + 1][0],
                            $volume_points[$i][$j + 1][0]
                        ], imagecolorat($img_png, (40 * $hd_ratio - 1) - $i, 20 * $hd_ratio + $j));
                        $this->polygons['torso']['front'][] = new Polygon([
                            $volume_points[$i][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                            $volume_points[$i][$j + 1][4 * $hd_ratio]
                        ], imagecolorat($img_png, 20 * $hd_ratio + $i, 20 * $hd_ratio + $j));
                    }
                }
                for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        $this->polygons['torso']['right'][] = new Polygon([
                            $volume_points[0][$j][$k],
                            $volume_points[0][$j][$k + 1],
                            $volume_points[0][$j + 1][$k + 1],
                            $volume_points[0][$j + 1][$k]
                        ], imagecolorat($img_png, 16 * $hd_ratio + $k, 20 * $hd_ratio + $j));
                        $this->polygons['torso']['left'][]  = new Polygon([
                            $volume_points[8 * $hd_ratio][$j][$k],
                            $volume_points[8 * $hd_ratio][$j][$k + 1],
                            $volume_points[8 * $hd_ratio][$j + 1][$k + 1],
                            $volume_points[8 * $hd_ratio][$j + 1][$k]
                        ], imagecolorat($img_png, (32 * $hd_ratio - 1) - $k, 20 * $hd_ratio + $j));
                    }
                }
                for ($i = 0; $i < 8 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        $this->polygons['torso']['top'][]    = new Polygon([
                            $volume_points[$i][0][$k],
                            $volume_points[$i + 1][0][$k],
                            $volume_points[$i + 1][0][$k + 1],
                            $volume_points[$i][0][$k + 1]
                        ], imagecolorat($img_png, 20 * $hd_ratio + $i, 16 * $hd_ratio + $k));
                        $this->polygons['torso']['bottom'][] = new Polygon([
                            $volume_points[$i][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                            $volume_points[$i][12 * $hd_ratio][$k + 1]
                        ], imagecolorat($img_png, 28 * $hd_ratio + $i, (20 * $hd_ratio - 1) - $k));
                    }
                }
                // RIGHT ARM
                $volume_points = [];
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                        if (!isset($volume_points[$i][$j][0])) {
                            $volume_points[$i][$j][0] = new Point([
                                'x' => $i - 4 * $hd_ratio,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => 0
                            ]);
                        }
                        if (!isset($volume_points[$i][$j][4 * $hd_ratio])) {
                            $volume_points[$i][$j][4 * $hd_ratio] = new Point([
                                'x' => $i - 4 * $hd_ratio,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => 4 * $hd_ratio
                            ]);
                        }
                    }
                }
                for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[0][$j][$k])) {
                            $volume_points[0][$j][$k] = new Point([
                                'x' => 0 - 4 * $hd_ratio,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[8 * $hd_ratio][$j][$k])) {
                            $volume_points[4 * $hd_ratio][$j][$k] = new Point([
                                'x' => 4 * $hd_ratio - 4 * $hd_ratio,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[$i][0][$k])) {
                            $volume_points[$i][0][$k] = new Point([
                                'x' => $i - 4 * $hd_ratio,
                                'y' => 0 + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[$i][12 * $hd_ratio][$k])) {
                            $volume_points[$i][12 * $hd_ratio][$k] = new Point([
                                'x' => $i - 4 * $hd_ratio,
                                'y' => 12 * $hd_ratio + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 4 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                        $this->polygons['rightArm']['back'][]  = new Polygon([
                            $volume_points[$i][$j][0],
                            $volume_points[$i + 1][$j][0],
                            $volume_points[$i + 1][$j + 1][0],
                            $volume_points[$i][$j + 1][0]
                        ], imagecolorat($img_png, (56 * $hd_ratio - 1) - $i, 20 * $hd_ratio + $j));
                        $this->polygons['rightArm']['front'][] = new Polygon([
                            $volume_points[$i][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                            $volume_points[$i][$j + 1][4 * $hd_ratio]
                        ], imagecolorat($img_png, 44 * $hd_ratio + $i, 20 * $hd_ratio + $j));
                    }
                }
                for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        $this->polygons['rightArm']['right'][] = new Polygon([
                            $volume_points[0][$j][$k],
                            $volume_points[0][$j][$k + 1],
                            $volume_points[0][$j + 1][$k + 1],
                            $volume_points[0][$j + 1][$k]
                        ], imagecolorat($img_png, 40 * $hd_ratio + $k, 20 * $hd_ratio + $j));
                        $this->polygons['rightArm']['left'][]  = new Polygon([
                            $volume_points[4 * $hd_ratio][$j][$k],
                            $volume_points[4 * $hd_ratio][$j][$k + 1],
                            $volume_points[4 * $hd_ratio][$j + 1][$k + 1],
                            $volume_points[4 * $hd_ratio][$j + 1][$k]
                        ], imagecolorat($img_png, (52 * $hd_ratio - 1) - $k, 20 * $hd_ratio + $j));
                    }
                }
                for ($i = 0; $i < 4 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        $this->polygons['rightArm']['top'][]    = new Polygon([
                            $volume_points[$i][0][$k],
                            $volume_points[$i + 1][0][$k],
                            $volume_points[$i + 1][0][$k + 1],
                            $volume_points[$i][0][$k + 1]
                        ], imagecolorat($img_png, 44 * $hd_ratio + $i, 16 * $hd_ratio + $k));
                        $this->polygons['rightArm']['bottom'][] = new Polygon([
                            $volume_points[$i][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                            $volume_points[$i][12 * $hd_ratio][$k + 1]
                        ], imagecolorat($img_png, 48 * $hd_ratio + $i, 16 * $hd_ratio + $k));
                    }
                }
                // LEFT ARM
                $volume_points = [];
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                        if (!isset($volume_points[$i][$j][0])) {
                            $volume_points[$i][$j][0] = new Point([
                                'x' => $i + 8 * $hd_ratio,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => 0
                            ]);
                        }
                        if (!isset($volume_points[$i][$j][4 * $hd_ratio])) {
                            $volume_points[$i][$j][4 * $hd_ratio] = new Point([
                                'x' => $i + 8 * $hd_ratio,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => 4 * $hd_ratio
                            ]);
                        }
                    }
                }
                for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[0][$j][$k])) {
                            $volume_points[0][$j][$k] = new Point([
                                'x' => 0 + 8 * $hd_ratio,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[8 * $hd_ratio][$j][$k])) {
                            $volume_points[4 * $hd_ratio][$j][$k] = new Point([
                                'x' => 4 * $hd_ratio + 8 * $hd_ratio,
                                'y' => $j + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[$i][0][$k])) {
                            $volume_points[$i][0][$k] = new Point([
                                'x' => $i + 8 * $hd_ratio,
                                'y' => 0 + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[$i][12 * $hd_ratio][$k])) {
                            $volume_points[$i][12 * $hd_ratio][$k] = new Point([
                                'x' => $i + 8 * $hd_ratio,
                                'y' => 12 * $hd_ratio + 8 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 4 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                        if ($this->isNewSkinType) {
                            $color1 = imagecolorat($img_png, 47 * $hd_ratio - $i, 52 * $hd_ratio + $j); // from right to left
                            $color2 = imagecolorat($img_png, 36 * $hd_ratio + $i, 52 * $hd_ratio + $j); // from left to right
                        } else {
                            $color1 = imagecolorat($img_png, (56 * $hd_ratio - 1) - ((4 * $hd_ratio - 1) - $i), 20 * $hd_ratio + $j);
                            $color2 = imagecolorat($img_png, 44 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), 20 * $hd_ratio + $j);
                        }

                        $this->polygons['leftArm']['back'][]  = new Polygon([
                            $volume_points[$i][$j][0],
                            $volume_points[$i + 1][$j][0],
                            $volume_points[$i + 1][$j + 1][0],
                            $volume_points[$i][$j + 1][0]
                        ], $color1);
                        $this->polygons['leftArm']['front'][] = new Polygon([
                            $volume_points[$i][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                            $volume_points[$i][$j + 1][4 * $hd_ratio]
                        ], $color2);
                    }
                }
                for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        if ($this->isNewSkinType) {
                            $color1 = imagecolorat($img_png, 32 * $hd_ratio + $k, 52 * $hd_ratio + $j); // from left to right
                            $color2 = imagecolorat($img_png, 43 * $hd_ratio - $k, 52 * $hd_ratio + $j); // from right to left
                        } else {
                            $color1 = imagecolorat($img_png, 40 * $hd_ratio + ((4 * $hd_ratio - 1) - $k), 20 * $hd_ratio + $j);
                            $color2 = imagecolorat($img_png, (52 * $hd_ratio - 1) - ((4 * $hd_ratio - 1) - $k), 20 * $hd_ratio + $j);
                        }

                        $this->polygons['leftArm']['right'][] = new Polygon([
                            $volume_points[0][$j][$k],
                            $volume_points[0][$j][$k + 1],
                            $volume_points[0][$j + 1][$k + 1],
                            $volume_points[0][$j + 1][$k]
                        ], $color1);
                        $this->polygons['leftArm']['left'][]  = new Polygon([
                            $volume_points[4 * $hd_ratio][$j][$k],
                            $volume_points[4 * $hd_ratio][$j][$k + 1],
                            $volume_points[4 * $hd_ratio][$j + 1][$k + 1],
                            $volume_points[4 * $hd_ratio][$j + 1][$k]
                        ], $color2);
                    }
                }
                for ($i = 0; $i < 4 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        if ($this->isNewSkinType) {
                            $color1 = imagecolorat($img_png, 36 * $hd_ratio + $i, 48 * $hd_ratio + $k); // from left to right
                            $color2 = imagecolorat($img_png, 40 * $hd_ratio + $i, 48 * $hd_ratio + $k); // from left to right
                        } else {
                            $color1 = imagecolorat($img_png, 44 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), 16 * $hd_ratio + $k);
                            $color2 = imagecolorat($img_png, 48 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), (20 * $hd_ratio - 1) - $k);
                        }

                        $this->polygons['leftArm']['top'][]    = new Polygon([
                            $volume_points[$i][0][$k],
                            $volume_points[$i + 1][0][$k],
                            $volume_points[$i + 1][0][$k + 1],
                            $volume_points[$i][0][$k + 1]
                        ], $color1);
                        $this->polygons['leftArm']['bottom'][] = new Polygon([
                            $volume_points[$i][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                            $volume_points[$i][12 * $hd_ratio][$k + 1]
                        ], $color2);
                    }
                }
                // RIGHT LEG
                $volume_points = [];
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                        if (!isset($volume_points[$i][$j][0])) {
                            $volume_points[$i][$j][0] = new Point([
                                'x' => $i,
                                'y' => $j + 20 * $hd_ratio,
                                'z' => 0
                            ]);
                        }
                        if (!isset($volume_points[$i][$j][4 * $hd_ratio])) {
                            $volume_points[$i][$j][4 * $hd_ratio] = new Point([
                                'x' => $i,
                                'y' => $j + 20 * $hd_ratio,
                                'z' => 4 * $hd_ratio
                            ]);
                        }
                    }
                }
                for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[0][$j][$k])) {
                            $volume_points[0][$j][$k] = new Point([
                                'x' => 0,
                                'y' => $j + 20 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[8 * $hd_ratio][$j][$k])) {
                            $volume_points[4 * $hd_ratio][$j][$k] = new Point([
                                'x' => 4 * $hd_ratio,
                                'y' => $j + 20 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[$i][0][$k])) {
                            $volume_points[$i][0][$k] = new Point([
                                'x' => $i,
                                'y' => 0 + 20 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[$i][12 * $hd_ratio][$k])) {
                            $volume_points[$i][12 * $hd_ratio][$k] = new Point([
                                'x' => $i,
                                'y' => 12 * $hd_ratio + 20 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 4 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                        $this->polygons['rightLeg']['back'][]  = new Polygon([
                            $volume_points[$i][$j][0],
                            $volume_points[$i + 1][$j][0],
                            $volume_points[$i + 1][$j + 1][0],
                            $volume_points[$i][$j + 1][0]
                        ], imagecolorat($img_png, (16 * $hd_ratio - 1) - $i, 20 * $hd_ratio + $j));
                        $this->polygons['rightLeg']['front'][] = new Polygon([
                            $volume_points[$i][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                            $volume_points[$i][$j + 1][4 * $hd_ratio]
                        ], imagecolorat($img_png, 4 * $hd_ratio + $i, 20 * $hd_ratio + $j));
                    }
                }
                for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        $this->polygons['rightLeg']['right'][] = new Polygon([
                            $volume_points[0][$j][$k],
                            $volume_points[0][$j][$k + 1],
                            $volume_points[0][$j + 1][$k + 1],
                            $volume_points[0][$j + 1][$k]
                        ], imagecolorat($img_png, 0 + $k, 20 * $hd_ratio + $j));
                        $this->polygons['rightLeg']['left'][]  = new Polygon([
                            $volume_points[4 * $hd_ratio][$j][$k],
                            $volume_points[4 * $hd_ratio][$j][$k + 1],
                            $volume_points[4 * $hd_ratio][$j + 1][$k + 1],
                            $volume_points[4 * $hd_ratio][$j + 1][$k]
                        ], imagecolorat($img_png, (12 * $hd_ratio - 1) - $k, 20 * $hd_ratio + $j));
                    }
                }
                for ($i = 0; $i < 4 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        $this->polygons['rightLeg']['top'][]    = new Polygon([
                            $volume_points[$i][0][$k],
                            $volume_points[$i + 1][0][$k],
                            $volume_points[$i + 1][0][$k + 1],
                            $volume_points[$i][0][$k + 1]
                        ], imagecolorat($img_png, 4 * $hd_ratio + $i, 16 * $hd_ratio + $k));
                        $this->polygons['rightLeg']['bottom'][] = new Polygon([
                            $volume_points[$i][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                            $volume_points[$i][12 * $hd_ratio][$k + 1]
                        ], imagecolorat($img_png, 8 * $hd_ratio + $i, 16 * $hd_ratio + $k));
                    }
                }
                // LEFT LEG
                $volume_points = [];
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                        if (!isset($volume_points[$i][$j][0])) {
                            $volume_points[$i][$j][0] = new Point([
                                'x' => $i + 4 * $hd_ratio,
                                'y' => $j + 20 * $hd_ratio,
                                'z' => 0
                            ]);
                        }
                        if (!isset($volume_points[$i][$j][4 * $hd_ratio])) {
                            $volume_points[$i][$j][4 * $hd_ratio] = new Point([
                                'x' => $i + 4 * $hd_ratio,
                                'y' => $j + 20 * $hd_ratio,
                                'z' => 4 * $hd_ratio
                            ]);
                        }
                    }
                }
                for ($j = 0; $j < 13 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[0][$j][$k])) {
                            $volume_points[0][$j][$k] = new Point([
                                'x' => 0 + 4 * $hd_ratio,
                                'y' => $j + 20 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[8 * $hd_ratio][$j][$k])) {
                            $volume_points[4 * $hd_ratio][$j][$k] = new Point([
                                'x' => 4 * $hd_ratio + 4 * $hd_ratio,
                                'y' => $j + 20 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 9 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 5 * $hd_ratio; $k++) {
                        if (!isset($volume_points[$i][0][$k])) {
                            $volume_points[$i][0][$k] = new Point([
                                'x' => $i + 4 * $hd_ratio,
                                'y' => 0 + 20 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                        if (!isset($volume_points[$i][12 * $hd_ratio][$k])) {
                            $volume_points[$i][12 * $hd_ratio][$k] = new Point([
                                'x' => $i + 4 * $hd_ratio,
                                'y' => 12 * $hd_ratio + 20 * $hd_ratio,
                                'z' => $k
                            ]);
                        }
                    }
                }
                for ($i = 0; $i < 4 * $hd_ratio; $i++) {
                    for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                        if ($this->isNewSkinType) {
                            $color1 = imagecolorat($img_png, 31 * $hd_ratio - $i, 52 * $hd_ratio + $j); // from right to left
                            $color2 = imagecolorat($img_png, 20 * $hd_ratio + $i, 52 * $hd_ratio + $j); // from left to right
                        } else {
                            $color1 = imagecolorat($img_png, (16 * $hd_ratio - 1) - ((4 * $hd_ratio - 1) - $i), 20 * $hd_ratio + $j);
                            $color2 = imagecolorat($img_png, 4 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), 20 * $hd_ratio + $j);
                        }

                        $this->polygons['leftLeg']['back'][]  = new Polygon([
                            $volume_points[$i][$j][0],
                            $volume_points[$i + 1][$j][0],
                            $volume_points[$i + 1][$j + 1][0],
                            $volume_points[$i][$j + 1][0]
                        ], $color1);
                        $this->polygons['leftLeg']['front'][] = new Polygon([
                            $volume_points[$i][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j][4 * $hd_ratio],
                            $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                            $volume_points[$i][$j + 1][4 * $hd_ratio]
                        ], $color2);
                    }
                }
                for ($j = 0; $j < 12 * $hd_ratio; $j++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        if ($this->isNewSkinType) {
                            $color1 = imagecolorat($img_png, 16 * $hd_ratio + $k, 52 * $hd_ratio + $j); // from left to right
                            $color2 = imagecolorat($img_png, 27 * $hd_ratio - $k, 52 * $hd_ratio + $j); // from right to left
                        } else {
                            $color1 = imagecolorat($img_png, 0 + ((4 * $hd_ratio - 1) - $k), 20 * $hd_ratio + $j);
                            $color2 = imagecolorat($img_png, (12 * $hd_ratio - 1) - ((4 * $hd_ratio - 1) - $k), 20 * $hd_ratio + $j);
                        }

                        $this->polygons['leftLeg']['right'][] = new Polygon([
                            $volume_points[0][$j][$k],
                            $volume_points[0][$j][$k + 1],
                            $volume_points[0][$j + 1][$k + 1],
                            $volume_points[0][$j + 1][$k]
                        ], $color1);
                        $this->polygons['leftLeg']['left'][]  = new Polygon([
                            $volume_points[4 * $hd_ratio][$j][$k],
                            $volume_points[4 * $hd_ratio][$j][$k + 1],
                            $volume_points[4 * $hd_ratio][$j + 1][$k + 1],
                            $volume_points[4 * $hd_ratio][$j + 1][$k]
                        ], $color2);
                    }
                }
                for ($i = 0; $i < 4 * $hd_ratio; $i++) {
                    for ($k = 0; $k < 4 * $hd_ratio; $k++) {
                        if ($this->isNewSkinType) {
                            $color1 = imagecolorat($img_png, 20 * $hd_ratio + $i, 48 * $hd_ratio + $k); // from left to right
                            $color2 = imagecolorat($img_png, 24 * $hd_ratio + $i, 48 * $hd_ratio + $k); // from left to right
                        } else {
                            $color1 = imagecolorat($img_png, 4 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), 16 * $hd_ratio + $k);
                            $color2 = imagecolorat($img_png, 8 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), (20 * $hd_ratio - 1) - $k);
                        }

                        $this->polygons['leftLeg']['top'][]    = new Polygon([
                            $volume_points[$i][0][$k],
                            $volume_points[$i + 1][0][$k],
                            $volume_points[$i + 1][0][$k + 1],
                            $volume_points[$i][0][$k + 1]
                        ], $color1);
                        $this->polygons['leftLeg']['bottom'][] = new Polygon([
                            $volume_points[$i][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k],
                            $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                            $volume_points[$i][12 * $hd_ratio][$k + 1]
                        ], $color2);
                    }
                }
            }
        }

        private function memberRotation(): void {
            foreach ($this->polygons['head'] as $face) {
                foreach ($face as $poly) {
                    $poly->preProject(4, 8, 2, $this->members_angles['head']['cos_alpha'], $this->members_angles['head']['sin_alpha'], $this->members_angles['head']['cos_omega'], $this->members_angles['head']['sin_omega']);
                }
            }

            if ($this->display_hair) {
                foreach ($this->polygons['helmet'] as $face) {
                    foreach ($face as $poly) {
                        $poly->preProject(4, 8, 2, $this->members_angles['head']['cos_alpha'], $this->members_angles['head']['sin_alpha'], $this->members_angles['head']['cos_omega'], $this->members_angles['head']['sin_omega']);
                    }
                }
            }

            if (!$this->head_only) {
                foreach ($this->polygons['rightArm'] as $face) {
                    foreach ($face as $poly) {
                        $poly->preProject(-2, 8, 2, $this->members_angles['rightArm']['cos_alpha'], $this->members_angles['rightArm']['sin_alpha'], $this->members_angles['rightArm']['cos_omega'], $this->members_angles['rightArm']['sin_omega']);
                    }
                }
                foreach ($this->polygons['leftArm'] as $face) {
                    foreach ($face as $poly) {
                        $poly->preProject(10, 8, 2, $this->members_angles['leftArm']['cos_alpha'], $this->members_angles['leftArm']['sin_alpha'], $this->members_angles['leftArm']['cos_omega'], $this->members_angles['leftArm']['sin_omega']);
                    }
                }
                foreach ($this->polygons['rightLeg'] as $face) {
                    foreach ($face as $poly) {
                        $poly->preProject(2, 20, ($this->members_angles['rightLeg']['sin_alpha'] < 0 ? 0 : 4), $this->members_angles['rightLeg']['cos_alpha'], $this->members_angles['rightLeg']['sin_alpha'], $this->members_angles['rightLeg']['cos_omega'], $this->members_angles['rightLeg']['sin_omega']);
                    }
                }
                foreach ($this->polygons['leftLeg'] as $face) {
                    foreach ($face as $poly) {
                        $poly->preProject(6, 20, ($this->members_angles['leftLeg']['sin_alpha'] < 0 ? 0 : 4), $this->members_angles['leftLeg']['cos_alpha'], $this->members_angles['leftLeg']['sin_alpha'], $this->members_angles['leftLeg']['cos_omega'], $this->members_angles['leftLeg']['sin_omega']);
                    }
                }
            }
        }


        private function createProjectionPlan(): void {
            foreach ($this->polygons as $piece) {
                foreach ($piece as $face) {
                    foreach ($face as $poly) {
                        if (!$poly->isProjected()) {
                            $poly->project();
                        }
                    }
                }
            }
        }

        /**
         * @param $output
         *
         * @return resource|string
         */
        private function displayImage($output) {
            global $minX, $maxX, $minY, $maxY;
            $cacheTime = 600;

            $width  = $maxX - $minX;
            $height = $maxY - $minY;
            $ratio  = $this->ratio;
            if ($ratio <= 0) {
                $ratio = 1;
            }

            if ($this->aa === true) {
                // double the ration for downscaling later (sort of AA)
                $ratio = $ratio * 2;
            }

            if ($cacheTime > 0) {
                $ts = gmdate('D, d M Y H:i:s', time() + $cacheTime) . ' GMT';
                if ($output !== 'return') {
                    header('Expires: ' . $ts);
                    header('Pragma: cache');
                    header('Cache-Control: max-age=' . $cacheTime);
                }
            }

            if ($this->format !== 'svg') {
                $srcWidth   = $ratio * $width + 1;
                $srcHeight  = $ratio * $height + 1;
                $realWidth  = $srcWidth / 2;
                $realHeight = $srcHeight / 2;

                $image = ImageHelper::createEmptyCanvas($srcWidth, $srcHeight);
            }

            $display_order = $this->getDisplayOrder();

            $imgOutput = '';
            if ($this->format === 'svg') {
                $imgOutput .= '<svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="' . $minX . ' ' . $minY . ' ' . $width . ' ' . $height . '">';
            }

            foreach ($display_order as $pieces) {
                foreach ($pieces as $piece => $faces) {
                    foreach ($faces as $face) {
                        foreach ($this->polygons[$piece][$face] as $poly) {
                            if ($this->format === 'svg') {
                                $imgOutput .= $poly->getSvgPolygon(1);
                            } else {
                                $poly->addPngPolygon($image, $minX, $minY, $ratio);
                            }
                        }
                    }
                }
            }

            if ($this->format === 'svg') {
                $imgOutput .= '</svg>';
            }

            if ($this->format !== 'svg') {
                if ($this->aa === true) {
                    // image normal size (sort of AA).
                    // resize the image down to it's normal size so it will be smoother
                    $destImage = ImageHelper::createEmptyCanvas($realWidth, $realHeight);

                    imagecopyresampled($destImage, $image, 0, 0, 0, 0, $realWidth, $realHeight, $srcWidth, $srcHeight);
                    $image = $destImage;
                }

                $imgData = null;
                if ($this->format === 'base64') {
                    // output png;base64
                    ob_start();
                    imagepng($image);
                    $imgData = ob_get_clean();
                } else {
                    $imgOutput = $image;
                }

                if ($imgData !== null) {
                    $imgOutput = base64_encode($imgData);
                    imagedestroy($image);
                }
            }

            return $imgOutput;
        }

        /**
         * @return array
         */
        private function getDisplayOrder(): array {
            $display_order = [];
            if (in_array('top', $this->front_faces, true)) {
                if (in_array('right', $this->front_faces, true)) {
                    $display_order[] = ['leftLeg' => $this->back_faces];
                    $display_order[] = ['leftLeg' => $this->visible_faces['leftLeg']['front']];
                    $display_order[] = ['rightLeg' => $this->back_faces];
                    $display_order[] = ['rightLeg' => $this->visible_faces['rightLeg']['front']];
                    $display_order[] = ['leftArm' => $this->back_faces];
                    $display_order[] = ['leftArm' => $this->visible_faces['leftArm']['front']];
                    $display_order[] = ['torso' => $this->back_faces];
                    $display_order[] = ['torso' => $this->visible_faces['torso']['front']];
                    $display_order[] = ['rightArm' => $this->back_faces];
                    $display_order[] = ['rightArm' => $this->visible_faces['rightArm']['front']];
                } else {
                    $display_order[] = ['rightLeg' => $this->back_faces];
                    $display_order[] = ['rightLeg' => $this->visible_faces['rightLeg']['front']];
                    $display_order[] = ['leftLeg' => $this->back_faces];
                    $display_order[] = ['leftLeg' => $this->visible_faces['leftLeg']['front']];
                    $display_order[] = ['rightArm' => $this->back_faces];
                    $display_order[] = ['rightArm' => $this->visible_faces['rightArm']['front']];
                    $display_order[] = ['torso' => $this->back_faces];
                    $display_order[] = ['torso' => $this->visible_faces['torso']['front']];
                    $display_order[] = ['leftArm' => $this->back_faces];
                    $display_order[] = ['leftArm' => $this->visible_faces['leftArm']['front']];
                }

                $display_order[] = ['helmet' => $this->back_faces];
                $display_order[] = ['head' => $this->back_faces];
                $display_order[] = ['head' => $this->visible_faces['head']['front']];
                $display_order[] = ['helmet' => $this->visible_faces['head']['front']];
            } else {
                $display_order[] = ['helmet' => $this->back_faces];
                $display_order[] = ['head' => $this->back_faces];
                $display_order[] = ['head' => $this->visible_faces['head']['front']];
                $display_order[] = ['helmet' => $this->visible_faces['head']['front']];

                if (in_array('right', $this->front_faces)) {
                    $display_order[] = ['leftArm' => $this->back_faces];
                    $display_order[] = ['leftArm' => $this->visible_faces['leftArm']['front']];
                    $display_order[] = ['torso' => $this->back_faces];
                    $display_order[] = ['torso' => $this->visible_faces['torso']['front']];
                    $display_order[] = ['rightArm' => $this->back_faces];
                    $display_order[] = ['rightArm' => $this->visible_faces['rightArm']['front']];
                    $display_order[] = ['leftLeg' => $this->back_faces];
                    $display_order[] = ['leftLeg' => $this->visible_faces['leftLeg']['front']];
                    $display_order[] = ['rightLeg' => $this->back_faces];
                    $display_order[] = ['rightLeg' => $this->visible_faces['rightLeg']['front']];
                } else {
                    $display_order[] = ['rightArm' => $this->back_faces];
                    $display_order[] = ['rightArm' => $this->visible_faces['rightArm']['front']];
                    $display_order[] = ['torso' => $this->back_faces];
                    $display_order[] = ['torso' => $this->visible_faces['torso']['front']];
                    $display_order[] = ['leftArm' => $this->back_faces];
                    $display_order[] = ['leftArm' => $this->visible_faces['leftArm']['front']];
                    $display_order[] = ['rightLeg' => $this->back_faces];
                    $display_order[] = ['rightLeg' => $this->visible_faces['rightLeg']['front']];
                    $display_order[] = ['leftLeg' => $this->back_faces];
                    $display_order[] = ['leftLeg' => $this->visible_faces['leftLeg']['front']];
                }
            }

            return $display_order;
        }


    }

    /**
     * Class img
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
