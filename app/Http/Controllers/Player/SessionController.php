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

    use App\Exceptions\HypixelFetchException;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\Player\Status\ViewStatusByUsernameRequest;
    use App\Utilities\HypixelAPI;
    use App\Utilities\MinecraftAvatar\MojangAPI;
    use Cache;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\RateLimiter;
    use Illuminate\Support\Facades\Redis;
    use Illuminate\View\View;
    use JsonException;
    use Plancke\HypixelPHP\cache\CacheTimes;
    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\player\Player;
    use Psr\SimpleCache\InvalidArgumentException;

    /**
     * Class SessionController
     *
     * @package App\Http\Controllers\Player
     */
    class SessionController extends Controller {
        public function getIndex(): View {
            $recentlyViewed = (new Collection(
                Redis::connection('cache')
                    ->zRevRangeByScore('recent_online_players', '+inf', '0', [
                        'withscores' => true, 'limit' => [0, 20]
                    ])
            ))->map(static fn($value, $key) => ['uuid' => $key, 'views' => $value] + Cache::get('recent_online_players.' . $key, []));

            return view('player.index', [
                'recently_viewed' => $recentlyViewed
            ]);
        }

        /**
         *
         * @throws JsonException
         * @throws InvalidArgumentException
         */
        public function redirectToStatus(ViewStatusByUsernameRequest $request): ?RedirectResponse {
            return $this->redirectToStatusByUsername($request->input('username'));
        }

        /**
         *
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        private function redirectToStatusByUsername(string $username): RedirectResponse {
            $mojangAPI = new MojangAPI();

            $data = $mojangAPI->getUUID($username);

            if (!$data['success']) {
                if ($data['status_code'] === 204) {
                    return redirect()->route('player.status.index')->withInput()->withErrors([
                        'username' => 'This username does not exist'
                    ]);
                }

                return redirect()->route('player.status.index')->withInput()->withErrors([
                    'username' => ($data['throttle'] ?? false) ? 'We\'re trying to use Mojang\'s API a bit too much right now, please try again later' : 'An unknown error has occurred while trying to retrieve your UUID from Mojang\'s servers'
                ]);
            }

            return redirect()->route('player.status', [$data['data']['id']]);
        }

        /**
         *
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        public function getStatusByUsername(string $username): RedirectResponse {
            return $this->redirectToStatusByUsername($username);
        }

        /**
         *
         * @return array|RedirectResponse|View
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         */
        public function getStatus(Request $request, string $uuid) {
            if (in_array($uuid, config('recents.blocklist.players'), true)) {
                return abort(404);
            }

            $api = new HypixelAPI();

            $api->getApi()->getCacheHandler()->setCacheTime(CacheTimes::PLAYER, 2 * 60);

            $player = $api->getPlayerByUuid($uuid);

            /** @var HypixelObject $player */
            if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                throw new HypixelFetchException('An unknown error has occurred while trying to fetch a Hypixel profile for ' . $uuid);
            }

            if ($player instanceof Player) {
                if (empty($player->getData())) {
                    return redirect()->route('player.status.index')->withErrors(['username' => 'This player does not exist on Hypixel']);
                }

                $status = $player->getStatus();

                $sessionEnabled = $player->get('settings.apiSession', true);
                $lastLogout     = $player->getInt('lastLogout');
                $lastLogin      = $player->getInt('lastLogin');

                $lastSeen = max($lastLogin, $lastLogout);

                if ($status !== null) {
                    $returnStatus = [
                        'online'     => $status->isOnline(),
                        'game'       => $status->getGameType() ? $status->getGameType()->getName() : null,
                        'mode'       => $status->getMode(),
                        'mode_fancy' => ucwords(str_replace('_', ' ', $status->getMode()))
                    ];
                } else {
                    $returnStatus = null;
                }

                Cache::set('recent_online_players.' . $uuid, [
                    'username' => $player->getName(),
                    'status'   => $returnStatus
                ], config('cache.times.recent_players'));

                if ($request->wantsJson()) {
                    return [
                        'status' => $returnStatus,
                        'player' => [
                            'last_logout' => $lastLogout,
                            'last_login'  => $lastLogin,
                            'last_seen'   => $lastSeen
                        ]
                    ];
                }

                RateLimiter::attempt(
                    "increase_recent_online_players_views:{$request->ip()}:{$uuid}",
                    1,
                    static function () use ($uuid) {
                        Redis::connection('cache')->zIncrBy('recent_online_players', 1, $uuid);
                    },
                    config('cache.times.recents_decay')
                );

                return view('player.status', [
                    'player'  => $player,
                    'status'  => $returnStatus,
                    'enabled' => $sessionEnabled,
                    'data'    => [
                        'last_logout' => $lastLogout,
                        'last_login'  => $lastLogin,
                        'last_seen'   => $lastSeen
                    ],
                    'urls'    => [
                        'get_status' => route('player.status.json', [$uuid])
                    ]
                ]);
            }

            throw new HypixelFetchException('Player data of player ' . $uuid . ' is empty.
            It is likely that we are currently being rate limited by the Hypixel API or that this player does not exist on Hypixel.');
        }
    }
