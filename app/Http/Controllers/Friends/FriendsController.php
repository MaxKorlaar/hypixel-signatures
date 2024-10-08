<?php
    /*
 * Copyright (c) 2021-2024 Max Korlaar
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

    namespace App\Http\Controllers\Friends;

    use App\Exceptions\HypixelFetchException;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\Friends\ViewListByUsernameRequest;
    use App\Jobs\Friends\LoadPlayerData;
    use App\Utilities\HypixelAPI;
    use App\Utilities\MinecraftAvatar\MojangAPI;
    use Cache;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Redis;
    use Illuminate\View\View;
    use JsonException;
    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\color\ColorUtils;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\exceptions\InvalidUUIDException;
    use Plancke\HypixelPHP\responses\player\Player;
    use Psr\SimpleCache\InvalidArgumentException;

    /**
     * Class FriendsController
     *
     * @package App\Http\Controllers\Friends
     */
    class FriendsController extends Controller {
        /**
         * @return View
         */
        public function getIndex(): View {
            $recentlyViewed = (new Collection(
                Redis::connection('cache')
                    ->zRevRangeByScore('recent_friends', '+inf', '0', [
                        'withscores' => true, 'limit' => [0, 20]
                    ])
            ))->map(static function ($value, $key) {
                return ['uuid' => $key, 'views' => $value] + Cache::get('recent_friends.' . $key, []);
            });

            return view('friends.index', [
                'recently_viewed' => $recentlyViewed
            ]);
        }

        /**
         * @param ViewListByUsernameRequest $request
         *
         * @return RedirectResponse
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        public function redirectToList(ViewListByUsernameRequest $request): ?RedirectResponse {
            return $this->redirectToListByUsername($request->input('username'));
        }

        /**
         * @param string $username
         *
         * @return RedirectResponse
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        private function redirectToListByUsername(string $username): RedirectResponse {
            $mojangAPI = new MojangAPI();

            $data = $mojangAPI->getUUID($username);

            if (!$data['success']) {
                if ($data['status_code'] === 204) {
                    return redirect()->route('friends')->withInput()->withErrors([
                        'username' => 'This username does not exist'
                    ]);
                }

                return redirect()->route('friends')->withInput()->withErrors([
                    'username' => ($data['throttle'] ?? false) ? 'We\'re trying to use Mojang\'s API a bit too much right now, please try again later' : 'An unknown error has occurred while trying to retrieve your UUID from Mojang\'s servers'
                ]);
            }

            return redirect()->route('friends.list', [$data['data']['id']]);
        }

        /**
         * @param $uuid
         *
         * @return Application|Factory|RedirectResponse|View
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         * @throws InvalidArgumentException
         */
        public function getFriends($uuid) {
            throw new HypixelFetchException(trans('friends.api_disabled_warning'));

            $api = new HypixelAPI();

            try {
                $player = $api->getPlayerByUuid($uuid);

                /** @var HypixelObject $player */
                if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                    return back(302, [], route('friends'))->withInput()->withErrors([
                        'username' => 'An unknown error has occurred while trying to fetch your profile from Hypixel'
                    ]);
                }

                if ($player instanceof Player) {
                    $friendsList = $this->getFriendsListJSON($uuid);

                    Redis::connection('cache')->zIncrBy('recent_friends', 1, $uuid);

                    Cache::set('recent_friends.' . $uuid, [
                        'username'      => $player->getName(),
                        'friends_count' => $friendsList['meta']['total_friends']
                    ], config('cache.times.recent_players'));

                    return view('friends.list', [
                            'username'           => $player->getName(),
                            'formatted_username' => ColorUtils::getColorParser()->parse($player->getRawFormattedName()),
                            'player'             => $player,
                            'urls'               => [
                                'get_friends' => route('friends.list.json', [$player->getUUID()])
                            ]
                        ] + $friendsList);
                }
            } catch (InvalidUUIDException $e) {
            }

            return back(302, [], route('friends'))->withInput()->withErrors([
                'username' => 'An unknown error has occurred while trying to fetch your profile from Hypixel'
            ]);
        }

        /**
         * @param string $uuid
         *
         * @param int    $max
         *
         * @return array[]|string[]
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         */
        public function getFriendsListJSON(string $uuid, int $max = 60): ?array {
            throw new HypixelFetchException(trans('friends.api_disabled_warning'));

            $api = new HypixelAPI();

            $player = $api->getPlayerByUuid($uuid);

            /** @var HypixelObject $player */
            if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                throw new HypixelFetchException('An unknown error has occurred while trying to fetch a Hypixel profile for ' . $uuid);
            }

            if ($player instanceof Player) {
                $friends = $player->getFriends();

                if ($friends === null) {
                    throw new HypixelFetchException("An unknown error has occurred while trying to retrieve {$player->getName()}'s friend list.
                    Unfortunately, " . config('app.name') . " is only allowed to request data from Hypixel's API a limited
                    amount of times per minute. You're likely seeing this error because we just ran into this limit. Please try to load
                    this page again in a few moments, or wait a few seconds for us to automatically try it again.");
                }

                $totalFriends = count($friends->getRawList());

                $loaded      = 0;
                $queued      = 0;
                $friendsList = [];

                foreach ($friends->getRawList() as $index => $friend) {
                    $friendUuid = $friend['uuidSender'] === $player->getUUID() ? $friend['uuidReceiver'] : $friend['uuidSender'];

                    $friendArray = [
                        'uuid'        => $friendUuid,
                        'since'       => $friend['started'],
                        'skin_url'    => route('player.skin.head', [$friendUuid]),
                        'friends_url' => route('friends.list', [$friendUuid]),
                    ];

                    if (Cache::has('hypixel.player.' . $friendUuid)) {
                        $loaded++;
                        $friendArray += Cache::get('hypixel.player.' . $friendUuid);
                    } else {
                        $friendArray['loading'] = true;

                        if ($queued < $max) {
                            Cache::remember('hypixel.player_load.' . $friendUuid, 60, static function () use ($friendUuid) {
                                LoadPlayerData::dispatch($friendUuid);
                            });
                        }

                        $queued++;
                    }

                    $friendsList[] = $friendArray;
                }

                return [
                    'friends' => $friendsList,
                    'meta'    => [
                        'total_friends' => $totalFriends,
                        'loaded'        => $loaded
                    ]
                ];
            }

            throw new HypixelFetchException("Player data of player {$uuid} is empty.
            Unfortunately, " . config('app.name') . " is only allowed to request data from Hypixel's API a limited
            amount of times per minute. You're likely seeing this error because we just ran into this limit. Please try to load
            this page again in a few moments, or wait a few seconds for us to automatically try it again.");
        }

        /**
         * @param string $username
         *
         * @return RedirectResponse
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        public function getFriendsByUsername(string $username): RedirectResponse {
            return $this->redirectToListByUsername($username);
        }
    }
