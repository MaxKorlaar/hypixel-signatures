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

namespace App\Http\Controllers\Player;

    use App\Exceptions\HypixelFetchException;
    use App\Http\Controllers\Controller;
    use App\Utilities\HypixelAPI;
    use Illuminate\Http\Request;
    use Illuminate\View\View;
    use Plancke\HypixelPHP\cache\CacheTimes;
    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class SessionController
     *
     * @package App\Http\Controllers\Player
     */
    class SessionController extends Controller {
        /**
         * @param Request $request
         * @param string  $uuid
         *
         * @return array|View
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         */
        public function getStatus(Request $request, string $uuid) {
            $api = new HypixelAPI();

            $api->getApi()->getCacheHandler()->setCacheTime(CacheTimes::PLAYER, 2 * 60);

            $player = $api->getPlayerByUuid($uuid);

            /** @var HypixelObject $player */
            if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                throw new HypixelFetchException('An unknown error has occurred while trying to fetch a Hypixel profile for ' . $uuid);
            }

            if ($player instanceof Player) {
                $status = $player->getStatus();

                $sessionEnabled = $player->get('settings.apiSession', true);
                $lastLogout     = $player->getInt('lastLogout');
                $lastLogin      = $player->getInt('lastLogin');

                $lastSeen = $lastLogin > $lastLogout ? $lastLogin : $lastLogout;

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

            throw new HypixelFetchException('Player data of player ' . $uuid . ' is empty');
        }
    }
