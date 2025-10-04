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

    use Carbon\Carbon;
    use DateTime;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Intervention\Image\Laravel\Facades\Image;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class TimestampSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    class TimestampSignatureController extends BaseSignature {

        protected function signature(Request $request, Player $player): Response {
            $image                  = BaseSignature::getImage(400, 100);
            $black                  = imagecolorallocate($image, 0, 0, 0);
            $fontSourceSansProLight = resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf');

            $username = $player->getName();

            imagettftext($image, 25, 0, 0, 25, $black, $fontSourceSansProLight, $username);

            $cacheTime   = Carbon::createFromTimestamp($player->getCachedTime());
            $currentTime = Carbon::now();

            imagettftext($image, 15, 0, 0, 50, $black, $fontSourceSansProLight, 'Last updated at: ' . $cacheTime->format(DateTime::RFC822)); // Total wins
            imagettftext($image, 15, 0, 0, 70, $black, $fontSourceSansProLight, 'Current date: ' . $currentTime->format(DateTime::RFC822)); // Total wins
            imagettftext($image, 15, 0, 0, 90, $black, $fontSourceSansProLight, 'Age of stored player data: ' . $cacheTime->diff($currentTime)->format('%dd %hh %im %ss')); // Total wins

            $this->addWatermark($image, $fontSourceSansProLight, 400, 100, 10); // Watermark/advertisement

            return response(Image::read($image)->encodeByExtension('png'))
                ->header('Content-Type', 'image/png')
                ->setCache([
                    'public'  => true,
                    'max_age' => 600
                ]);
        }
    }
