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

    namespace App\Http\Controllers;

    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\Routing\Redirector;
    use Illuminate\View\View;

    /**
     * Class IndexController
     *
     * @package App\Http\Controllers
     */
    class IndexController extends Controller {
        public function index(): View {
            return view('index');
        }

        /**
         * @return Factory|View
         */
        public function signatureIndex() {
            $signatures = [
                'generic'  => [
                    'name'        => trans('signatures.groups.generic.name'),
                    'short_name'  => trans('signatures.groups.generic.short_name'),
                    'description' => trans('signatures.groups.generic.description'),
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
                            'name'      => 'General statistics (tooltip)',
                            'route'     => 'general_tooltip',
                            'pixelated' => true
                        ],
                        [
                            'name'  => 'Bed Wars statistics',
                            'route' => 'bedwars'
                        ],
                        [
                            'name'  => 'UHC Champions statistics',
                            'route' => 'uhc_champions'
                        ],
                        [
                            'name'  => 'Duels statistics',
                            'route' => 'duels'
                        ],
                        [
                            'name'  => 'TNT-Games statistics',
                            'route' => 'tnt_games'
                        ],
                        [
                            'name'  => 'Cops and Crims statistics',
                            'route' => 'cops_and_crims'
                        ],
                        [
                            'name'  => 'Blitz Survival Games statistics',
                            'route' => 'blitz_survival_games'
                        ],
                    ]
                ],
                'skywars'  => [
                    'name'        => trans('signatures.groups.skywars.name'),
                    'short_name'  => trans('signatures.groups.skywars.short_name'),
                    'description' => trans('signatures.groups.skywars.description'),
                    'signatures'  => [
                        [
                            'name'         => 'SkyWars statistics',
                            'route'        => 'skywars',
                            'options_text' => "If you prefer to show off the amount of players you have survived instead of your SkyWars level,
                            then add the parameter 'show_survived_players' to the URL and set it to 'true'.
                            For example: <code>" .
                                route('signatures.skywars', [':uuid', 'show_survived_players' => 'true'])
                                . '</code>',
                        ],
                        [
                            'name'  => 'Simple SkyWars statistics',
                            'route' => 'skywars_simple'
                        ],
                        [
                            'name'  => 'SkyWars statistics per mode (GIF)',
                            'route' => 'skywars_gif'
                        ],
                    ]
                ],
                'guild'    => [
                    'name'        => trans('signatures.groups.guild.name'),
                    'short_name'  => trans('signatures.groups.guild.short_name'),
                    'description' => trans('signatures.groups.guild.description'),
                    'signatures'  => [
                        [
                            'name'  => 'General guild statistics',
                            'route' => 'guild.general'
                        ],
                        [
                            'name'  => 'Guild banner and name',
                            'route' => 'guild.banner'
                        ]
                    ]
                ],
                'skyblock' => [
                    'name'        => trans('signatures.groups.skyblock.name'),
                    'short_name'  => trans('signatures.groups.skyblock.short_name'),
                    'description' => trans('signatures.groups.skyblock.description'),
                    'signatures'  => [
                        [
                            'name'       => 'SkyBlock character stats',
                            'route'      => 'skyblock.stats',
                            'parameters' => [':skyblock_profile'],
                        ],
                        [
                            'name'         => 'SkyBlock pet levels',
                            'route'        => 'skyblock.pets',
                            'parameters'   => [':skyblock_profile'],
                            'options_text' => "By default the pets are ordered by rarity, with the currently active pet always as the first pet shown.
                             If you would like to sort your pets based on their level regardless of their rarity, add the parameter
                             'sort' to the image URL and set it to 'level'. To disable the highlighting of your active pet,
                             set the parameter 'highlight_active' to 'false'. For example: <code>" .
                                route('signatures.skyblock.pets', [':uuid', ':skyblock_profile', 'sort' => 'level', 'highlight_active' => 'false'])
                                . '</code>',
                        ],
                        [
                            'name'         => 'SkyBlock minion levels',
                            'route'        => 'skyblock.minions',
                            'parameters'   => [':skyblock_profile'],
                            'options_text' => "For people with a large amount of minions, this signature defaults to a smaller size.
                            To override this, you may optionally set the parameter 'size' to a number between 1 and 5.
                            You may also specify how many minions are shown per row with the parameter 'per_row', which defaults to 15, and should be a number between 5 and 50.
                            For example: <code>" .
                                route('signatures.skyblock.minions', [':uuid', ':skyblock_profile', 'size' => 4, 'per_row' => 20])
                                . '</code>',
                        ]
                    ]
                ]
            ];

            foreach ($signatures as &$signatureGroup) {
                foreach ($signatureGroup['signatures'] as &$signature) {
                    $signature['url'] = route('signatures.' . $signature['route'], [':uuid', ... $signature['parameters'] ?? []]);
                }
            }

            return view('signatures.index', [
                'signatures' => $signatures,
                'urls'       => [
                    'get_uuid'              => route('player.get_uuid', [':username']),
                    'get_profile'           => route('player.get_profile', [':uuid']),
                    'get_skyblock_profiles' => route('skyblock.get_profiles', [':uuid'])
                ]
            ]);
        }

        /**
         *
         * @return Application|RedirectResponse|Redirector
         */
        public function redirectToSignatures(Request $request): RedirectResponse {
            return redirect(route('signatures') . '#' . $request->input('username'));
        }
    }
