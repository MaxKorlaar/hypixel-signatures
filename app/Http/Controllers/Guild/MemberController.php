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

    namespace App\Http\Controllers\Guild;

    use App\Exceptions\HypixelFetchException;
    use App\Http\Controllers\Controller;
    use App\Jobs\Guild\LoadMemberData;
    use App\Utilities\HypixelAPI;
    use Cache;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Http\Request;
    use Illuminate\Support\Str;
    use Illuminate\View\View;
    use Plancke\HypixelPHP\color\ColorUtils;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\guild\Guild;
    use Plancke\HypixelPHP\responses\guild\GuildMember;

    /**
     * Class MemberController
     *
     * @package App\Http\Controllers\Guild
     */
    class MemberController extends Controller {
        /**
         * @param Request $request
         * @param string  $nameOrId
         *
         * @return array[]|Application|Factory|View
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         */
        public function getMembers(Request $request, string $nameOrId) {
            $HypixelAPI = new HypixelAPI();

            if (HypixelAPI::isValidMongoId($nameOrId)) {
                $guild = $HypixelAPI->getGuildById($nameOrId);
            } else {
                $guild = $HypixelAPI->getGuildByName($nameOrId);
            }

            if ($guild instanceof Guild) {
                $memberList = $this->getMemberList($guild);

                if ($request->wantsJson()) {
                    return $memberList;
                }

                return view('guild.members', [
                        'guild' => $guild,
                        'urls'  => [
                            'get_members' => route('guild.members.json', [$guild->getID()])
                        ]
                    ] + $memberList);
            }

            throw new HypixelFetchException('An unknown error has occurred while trying to fetch this Guild or its members from Hypixel');
        }

        /**
         * @param Guild         $guild
         *
         * @param callable|null $playerCallback
         *
         * @return array
         * @throws HypixelPHPException
         */
        protected function getMemberList(Guild $guild, callable $playerCallback = null): array {
            $memberList   = [];
            $totalMembers = $guild->getMemberCount();
            $loaded       = 0;
            $queued       = 0;
            $max          = 50;

            $members = $guild->getMemberList();
            $list    = $members->getList();

            uksort($list, static function ($a, $b) {
                if (Str::is(['guildmaster', 'guild master'], strtolower($b))) {
                    return 1;
                }

                return 0;
            });

            foreach ($list as $rank => $rankMembers) {
                /** @var GuildMember $member */
                foreach ($rankMembers as $member) {
                    $uuid = $member->getUUID();

                    $memberArray = $member->getData() + [
                            'skin_url' => route('player.skin.head', [$uuid, 'size' => 3]),
                            'loading'  => false
                        ];

                    if (Cache::has('hypixel.players_loaded. ' . $uuid)) {
                        $loaded++;
                        $player = $member->getPlayer();

                        $memberArray['formatted_name'] = ColorUtils::getColorParser()->parse($player->getRawFormattedName());
                        $memberArray['last_login']     = $player->getInt('lastLogin');
                        $memberArray['name']           = $player->getName();

                        if ($playerCallback !== null) {
                            $memberArray += $playerCallback($player);
                        }
                    } else {
                        $memberArray['loading'] = true;

                        if ($queued < $max) {
                            Cache::remember('hypixel.guild_player_load.' . $uuid, 60, static function () use ($uuid) {
                                LoadMemberData::dispatch($uuid);
                            });
                        }

                        $queued++;
                    }

                    $memberList[] = $memberArray;
                }
            }

            return [
                'members' => $memberList,
                'meta'    => [
                    'total_members' => $totalMembers,
                    'loaded'        => $loaded
                ]
            ];
        }
    }
