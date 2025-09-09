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

    use Cache;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Redis;
    use Illuminate\View\View;

    /**
     * Class MetaController
     *
     * @package App\Http\Controllers
     */
    class MetaController extends Controller {
        /**
         * @return Application|Factory|View
         */
        public function getPrivacyPage(): View {
            return view('privacy');
        }

        /**
         * @todo Implement sitemap generator
         */
        public function getSitemap() {
            $pages = new Collection([
                [
                    'url'      => route('home'),
                    'priority' => 1
                ],
                [
                    'url'      => route('signatures'),
                    'priority' => 1
                ],
                [
                    'url'       => route('guild'),
                    'priority'  => 1,
                    'frequency' => 'daily',
                ],
                [
                    'url'       => route('player.status.index'),
                    'priority'  => 1,
                    'frequency' => 'daily',
                ],
            ]);

            $recentGuilds = new Collection(
                Redis::connection('cache')
                    ->zRevRangeByScore('recent_guilds', '+inf', '0', [
                        'withscores' => true
                    ])
            )->map(static function ($value, $key) {
                $guildData = Cache::get('recent_guilds.' . $key, [
                    'name' => $key
                ]);

                if (!isset($guildData['name'])) {
                    return [];
                }

                return [
                    [
                        'url'       => route('guild.info', [$guildData['name']]),
                        'frequency' => 'daily',
                        'priority'  => .9
                    ],
                    [
                        'url'       => route('guild.members', [$guildData['name']]),
                        'frequency' => 'daily',
                        'priority'  => .85
                    ],
                    [
                        'url'       => route('guild.games.skywars', [$guildData['name']]),
                        'frequency' => 'daily',
                        'priority'  => .80
                    ],
                    [
                        'url'       => route('guild.games.bedwars', [$guildData['name']]),
                        'frequency' => 'daily',
                        'priority'  => .80
                    ],
                    [
                        'url'       => route('guild.games.tntgames', [$guildData['name']]),
                        'frequency' => 'daily',
                        'priority'  => .80
                    ]
                ];
            })->flatten(1);

            $recentOnlinePlayers = new Collection(
                Redis::connection('cache')
                    ->zRevRangeByScore('recent_online_players', '+inf', '0', [
                        'withscores' => true
                    ])
            )->map(static fn($value, $uuid) => [
                'url'       => route('player.status', [$uuid]),
                'frequency' => 'daily',
                'priority'  => .6
            ]);

            return response(view('meta.sitemap', [
                'pages' => $pages->concat($recentGuilds)->concat($recentOnlinePlayers)
            ]), 200, [
                'Content-Type' => 'text/xml'
            ]);
        }
    }
