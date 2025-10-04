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

    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Intervention\Image\Laravel\Facades\Image;
    use Plancke\HypixelPHP\classes\serverType\ServerTypes;
    use Plancke\HypixelPHP\responses\player\GameStats;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class SimpleSkyWarsSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class SimpleSkyWarsSignatureController extends BaseSignature {

        /**
         * @inheritDoc
         */
        protected function signature(Request $request, Player $player): Response {
            $image = BaseSignature::getImage(400, 175);

            $fontNeutonRegular = resource_path('fonts/Neuton/Neuton-Regular.ttf');
            $fontNeutonLight   = resource_path('fonts/Neuton/Neuton-Light.ttf');
            $textColor         = imagecolorallocate($image, 233, 205, 153);
            $grey              = imagecolorallocate($image, 203, 203, 203);

            $mainStats = $player->getStats();
            /** @var GameStats $stats */
            $stats = $mainStats->getGameFromID(ServerTypes::SKYWARS);

            $wins   = $stats->getInt('wins');
            $kills  = $stats->getInt('kills');
            $deaths = $stats->getInt('deaths');

            $kd = $deaths !== 0 ? round($kills / $deaths, 2) : 'None';

            $artwork = imagecreatefrompng(resource_path('images/hypixel/artwork/Skywars-175.png'));

            imagecopy($image, $artwork, 0, 0, 0, 0, 175, 175);
            imagedestroy($artwork);

            $textBegin = 175;

            imagettftext($image, 30, 0, $textBegin, 46, $textColor, $fontNeutonRegular, number_format($wins) . ' wins');
            imagettftext($image, 30, 0, $textBegin, 92, $textColor, $fontNeutonRegular, number_format($kills) . ' kills');
            imagettftext($image, 30, 0, $textBegin, 138, $textColor, $fontNeutonRegular, $kd . ' k/d ratio');

            imagettftext($image, 12, 0, $textBegin, 165, $grey, $fontNeutonLight, (string) config('signatures.watermark')); // Watermark/advertisement

            return response(Image::read($image)->encodeByExtension('png'))
                ->header('Content-Type', 'image/png')
                ->setCache([
                    'public'  => true,
                    'max_age' => 600
                ]);
        }
    }
