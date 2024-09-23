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

namespace App\Http\Controllers\Guild;

    use App\Http\Controllers\Controller;
    use App\Http\Controllers\Signatures\BaseSignature;
    use App\Utilities\HypixelAPI;
    use App\Utilities\Minecraft\Banner;
    use Illuminate\Http\Response;
    use Intervention\Image\Laravel\Facades\Image;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\guild\Guild;

    /**
     * Class BannerController
     *
     * @package App\Http\Controllers\Guild
     */
    class BannerController extends Controller {
        /**
         * @param string $guildId
         *
         * @return Response
         * @throws HypixelPHPException
         */
        public function getBanner(string $guildId): Response {
            $HypixelAPI = new HypixelAPI();

            $guild = $HypixelAPI->getGuildById($guildId);

            $bannerData = [];

            if ($guild instanceof Guild) {
                $bannerData = $guild->getBanner();
            }

            if (empty($bannerData)) {
                // https://www.planetminecraft.com/banner/hypixel-cape/
                $bannerData = [
                    'Base'     => 14,
                    'Patterns' => [
                        [
                            'Color'   => 0,
                            'Pattern' => 'cs'
                        ],
                        [
                            'Color'   => 14,
                            'Pattern' => 'ms'
                        ],
                        [
                            'Color'   => 0,
                            'Pattern' => 'bts'
                        ],
                        [
                            'Color'   => 0,
                            'Pattern' => 'tts'
                        ],
                        [
                            'Color'   => 0,
                            'Pattern' => 'bo'
                        ],
                        [
                            'Color'   => 0,
                            'Pattern' => 'gru'
                        ]
                    ]
                ];
            }

            $banner      = new Banner($bannerData);
            $bannerImage = $banner->generate();

            $bannerWidth  = imagesx($bannerImage);
            $bannerHeight = imagesy($bannerImage);
            $size         = 5;

            $image = BaseSignature::getImage($bannerWidth * $size, $bannerHeight * $size);

            imagecopyresized($image, $bannerImage, 0, 0, 0, 0, $bannerWidth * $size, $bannerHeight * $size, $bannerWidth, $bannerHeight);
            imagedestroy($bannerImage);

            return response(Image::read($image)->encodeByExtension('png'))
                ->header('Content-Type', 'image/png')
                ->setCache([
                    'public'  => true,
                    'max_age' => config('cache.times.public.guild_banner')
                ]);
        }
    }
