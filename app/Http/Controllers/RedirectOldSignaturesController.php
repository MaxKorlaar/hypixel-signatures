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

    namespace App\Http\Controllers;

    use App\Http\Controllers\Signatures\BaseSignature;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Response;
    use Illuminate\Routing\Redirector;
    use Illuminate\Support\Str;

    /**
     * Class RedirectOldSignaturesController
     *
     * @package App\Http\Controllers
     */
    class RedirectOldSignaturesController extends Controller {
        private const URL_MAPPING = [
            'main'           => 'signatures.general',
            'main-small'     => 'signatures.general_small',
            'tooltip'        => 'signatures.general_tooltip',
            'bed-wars'       => 'signatures.bedwars',
            'skywars'        => 'signatures.skywars',
            'skywars-simple' => 'signatures.skywars_simple',
            'skywars-gif'    => 'signatures.skywars_gif',
            'uhc-champions'  => 'signatures.uhc_champions',
            'tnt-games'      => 'signatures.tnt_games'
        ];

        /**
         * @return Application|RedirectResponse|Redirector
         */
        public function redirectIndex() {
            return redirect(route('signatures'), 301);
        }

        /**
         * @param      $oldSignatureName
         * @param      $uuid
         * @param null $other
         *
         * @return Application|RedirectResponse|Response|Redirector
         */
        public function redirect($oldSignatureName, $uuid, $other = null) {
            $lowercaseOldSignatureName = strtolower($oldSignatureName);

            if (isset(self::URL_MAPPING[$lowercaseOldSignatureName])) {
                return redirect(route(self::URL_MAPPING[$lowercaseOldSignatureName], [$uuid]), 301);
            }

            $oldSignatureName = Str::limit($oldSignatureName, 20);

            return BaseSignature::generateErrorImage("Signature '{$oldSignatureName}' does not exist. Got this signature from hypixel.maxkorlaar.com? It might have been removed. You can suggest it to be added again. Visit Hypixel.Paniek.de or the forum thread.", 404, 800, 250);
        }
    }
