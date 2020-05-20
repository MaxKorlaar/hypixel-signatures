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

    namespace App\Http\Controllers;

    use Illuminate\Contracts\View\Factory;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\View\View;

    /**
     * Class IndexController
     *
     * @package App\Http\Controllers
     */
    class IndexController extends Controller {
        /**
         * @return RedirectResponse
         */
        public function index(): RedirectResponse {
            return redirect()->route('signatures');
        }

        /**
         * @return Factory|View
         */
        public function signatureIndex() {
            $signatures = [
                'generic'  => [
                    'name'        => 'Generic Signatures',
                    'short_name'  => 'Generic',
                    'description' => 'Generic statistics for the most popular Hypixel games or your Hypixel profile.',
                    'signatures'  => [
                        [
                            'name'  => 'General statistics',
                            'route' => 'general'
                        ],
                        [
                            'name'  => 'General statistics (small)',
                            'route' => 'general_small'
                        ],
                        [
                            'name'  => 'General statistics (tooltip)',
                            'route' => 'general_tooltip'
                        ],
                        [
                            'name'  => 'SkyWars statistics',
                            'route' => 'skywars'
                        ],
                        [
                            'name'  => 'BedWars statistics',
                            'route' => 'bedwars'
                        ],
                    ]
                ],
                'skyblock' => [
                    'name'        => 'SkyBlock Signatures',
                    'short_name'  => 'SkyBlock',
                    'description' => 'Hypixel SkyBlock statistics, custom made to parse all SkyBlock data per SkyBlock profile on your account!',
                    'signatures'  => [
                        [
                            'name'         => 'SkyBlock pet levels',
                            'route'        => 'skyblock.pets',
                            'parameters'   => [':skyblock_profile'],
                            'options_text' => "By default the pets are ordered by rarity, with the currently active pet always as the first pet shown.
                             If you would like to sort your pets based on their level regardless of their rarity, add the parameter
                             'sort' to the image URL and set it to 'level'. For example: <code>" .
                                route('signatures.skyblock.pets', ['uuid', 'profile-id', 'sort' => 'level'])
                                . "</code>",
                        ]
                    ]
                ]
            ];

            foreach ($signatures as &$signatureGroup) {
                foreach ($signatureGroup['signatures'] as &$signature) {
                    $signature['url'] = route('signatures.' . $signature['route'], [':username', ... $signature['parameters'] ?? []]);
                }
            }

            //route('signatures.' ~ signature.route, ['b876ec32e396476ba1158438d83c67d4'])

            return view('signatures.index', [
                'signatures' => $signatures,
                'urls'       => [
                    'get_uuid'              => route('player.get_uuid', [':username']),
                    'get_profile'           => route('player.get_profile', [':uuid']),
                    'get_skyblock_profiles' => route('skyblock.get_profiles', [':uuid'])
                ]
            ]);
        }
    }
