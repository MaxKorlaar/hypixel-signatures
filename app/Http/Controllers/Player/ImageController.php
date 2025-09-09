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

    namespace App\Http\Controllers\Player;

    use App\Http\Controllers\Controller;
    use App\Utilities\MinecraftAvatar\ThreeDAvatar;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Intervention\Image\Laravel\Facades\Image;

    /**
     * Class ImageController
     *
     * @package App\Http\Controllers\Player
     */
    class ImageController extends Controller {
        public function getHeadAsWebP(Request $request, string $uuid): Response {
            $headImage = $this->getHead($request, $uuid);

            return response(Image::read($headImage)->encodeByExtension('webp'))
                ->header('Content-Type', 'image/webp')
                ->setCache([
                    'public'  => true,
                    'max_age' => config('cache.times.public.player_skin')
                ]);
        }

        /**
         *
         * @return bool|resource
         */
        private function getHead(Request $request, string $uuid) {
            return (new ThreeDAvatar())->getThreeDSkinFromCache($uuid, min((int)$request->input('size', 5), 20), 45, true, true, true, -30);
        }

        public function getHeadAsPNG(Request $request, string $uuid): Response {
            $headImage = $this->getHead($request, $uuid);

            return response(Image::read($headImage)->encodeByExtension('png'))
                ->header('Content-Type', 'image/png')
                ->setCache([
                    'public'  => true,
                    'max_age' => config('cache.times.public.player_skin')
                ]);
        }

        public function getSkinAsWebP(Request $request, string $uuid): Response {
            $skin = $this->getSkin($request, $uuid);

            return response(Image::read($skin)->encodeByExtension('webp'))
                ->header('Content-Type', 'image/webp')
                ->setCache([
                    'public'  => true,
                    'max_age' => config('cache.times.public.player_skin')
                ]);
        }

        /**
         *
         * @return bool|resource
         */
        private function getSkin(Request $request, string $uuid) {
            return (new ThreeDAvatar())->getThreeDSkinFromCache($uuid, min((int)$request->input('size', 5), 20), 45, false, true, true, -30);
        }

        public function getSkinAsPNG(Request $request, string $uuid): Response {
            $skin = $this->getSkin($request, $uuid);

            return response(Image::read($skin)->encodeByExtension('png'))
                ->header('Content-Type', 'image/png')
                ->setCache([
                    'public'  => true,
                    'max_age' => config('cache.times.public.player_skin')
                ]);
        }
    }
