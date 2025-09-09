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
        public function getIndex(): View {
            $recentlyViewed = new Collection(
                Redis::connection('cache')
                    ->zRevRangeByScore('recent_friends', '+inf', '0', [
                        'withscores' => true, 'limit' => [0, 20]
                    ])
            )->map(static fn($value, $key) => ['uuid' => $key, 'views' => $value] + Cache::get('recent_friends.' . $key, []));

            return view('friends.index', [
                'recently_viewed' => $recentlyViewed
            ]);
        }

        /**
         *
         * @return RedirectResponse
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        public function redirectToList(ViewListByUsernameRequest $request): ?RedirectResponse {
            return $this->redirectToListByUsername($request->input('username'));
        }

        /**
         *
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
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         * @throws InvalidArgumentException
         */
        public function getFriends($uuid): never {
            throw new HypixelFetchException(trans('friends.api_disabled_warning'));
        }

        /**
         *
         *
         * @return array[]|string[]
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         */
        public function getFriendsListJSON(string $uuid, int $max = 60): ?array {
            throw new HypixelFetchException(trans('friends.api_disabled_warning'));
        }

        /**
         *
         * @throws InvalidArgumentException
         * @throws JsonException
         */
        public function getFriendsByUsername(string $username): RedirectResponse {
            return $this->redirectToListByUsername($username);
        }
    }
