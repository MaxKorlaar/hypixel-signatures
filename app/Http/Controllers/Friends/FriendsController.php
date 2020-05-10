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

    namespace App\Http\Controllers\Friends;

    use App\Http\Controllers\Controller;
    use App\Jobs\Friends\LoadPlayerData;
    use App\Utilities\HypixelAPI;
    use Cache;
    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\exceptions\InvalidUUIDException;
    use Plancke\HypixelPHP\fetch\Response;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class FriendsController
     *
     * @package App\Http\Controllers\Friends
     */
    class FriendsController extends Controller {
        /**
         * @param $uuid
         *
         * @return HypixelObject|Response|Player|string[]|null
         * @throws HypixelPHPException
         */
        public function getFriends($uuid) {
            $api    = new HypixelAPI();
            $player = $api->getPlayerByUuid($uuid);
            try {
                $player = $api->getPlayerByUuid($uuid);

                /** @var HypixelObject $player */
                if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                    return ["Bad API response.\n{$player->getResponse()->getData()['cause']}"];
                }

                if ($player instanceof Player) {
                    if (empty($player->getData())) {
                        return ['Player has no public data.'];
                    }
                    $friends = $player->getFriends();
                    if ($friends === null) {
                        return ['Something went wrong'];
                    }
                    $totalFriends = count($friends->getList());

                    $loaded = 0;
                    foreach ($friends->getRawList() as $friend) {
                        $friendUuid = $friend['uuidSender'] === $player->getUUID() ? $friend['uuidReceiver'] : $friend['uuidSender'];
                        if (Cache::has('hypixel.player.' . $friendUuid)) {
                            $loaded++;
                            echo Cache::get('hypixel.player.' . $friendUuid) . '<br>';
                        } else {
                            Cache::remember('hypixel.player_load.' . $friendUuid, 300, static function () use ($friendUuid) {
                                LoadPlayerData::dispatch($friendUuid);
                            });
                            echo $friendUuid . '<br>';
                        }
                    }

                    dd($totalFriends, $loaded . '/' . $totalFriends);
                }

                return ['Unexpected API response.'];
            } catch (InvalidUUIDException $exception) {
                return ['UUID is invalid.'];
            } catch (HypixelPHPException $e) {
                return ['Unknown: ' . $e->getMessage()];
            }
        }
    }
