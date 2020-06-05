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

namespace App\Http\Controllers\Guild;

    use App\Exceptions\HypixelFetchException;
    use App\Utilities\HypixelAPI;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Http\Request;
    use Illuminate\View\View;
    use Plancke\HypixelPHP\classes\gameType\GameTypes;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\guild\Guild;
    use Plancke\HypixelPHP\responses\player\GameStats;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class GamesController
     *
     * @package App\Http\Controllers\Guild
     */
    class SkyWarsController extends MemberController {
        /**
         * @param Request $request
         * @param string  $nameOrId
         *
         * @return array[]|Application|Factory|View
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         */
        public function getSkyWarsStatistics(Request $request, string $nameOrId) {
            $HypixelAPI = new HypixelAPI();

            if (HypixelAPI::isValidMongoId($nameOrId)) {
                $guild = $HypixelAPI->getGuildById($nameOrId);
            } else {
                $guild = $HypixelAPI->getGuildByName($nameOrId);
            }

            if ($guild instanceof Guild) {
                $memberList = $this->getSkyWarsMemberList($guild);

                if ($request->wantsJson()) {
                    return $memberList;
                }

                return view('guild.games.skywars', [
                        'guild' => $guild,
                        'urls'  => [
                            'get_members' => route('guild.games.skywars.json', [$guild->getID()])
                        ]
                    ] + $memberList);
            }

            throw new HypixelFetchException('An unknown error has occurred while trying to fetch this Guild or its members from Hypixel');
        }


        /**
         * @param Guild $guild
         *
         * @return array[]
         * @throws HypixelPHPException
         */
        private function getSkyWarsMemberList(Guild $guild): array {
            return $this->getMemberList($guild, static function (Player $player) {
                /** @var GameStats $stats */
                $stats = $player->getStats()->getGameFromID(GameTypes::SKYWARS);

                $kills = $stats->getInt('kills');
                if ($stats->getInt('deaths') > 0) {
                    $kd = round($kills / $stats->getInt('deaths'), 2);
                } else {
                    $kd = 'N/A';
                }

                $wins = $stats->getInt('wins');
                if (($wins + $stats->getInt('losses')) > 0) {
                    $winsPercentage = round(($wins / ($wins + $stats->getInt('losses'))) * 100, 1);
                } else {
                    $winsPercentage = 0;
                }

                $killsSolo = $stats->getInt('kills_solo');
                if ($stats->getInt('deaths_solo') > 0) {
                    $kdSolo = round($killsSolo / $stats->getInt('deaths_solo'), 2);
                } else {
                    $kdSolo = 'N/A';
                }

                $winsSolo = $stats->getInt('wins_solo');
                if (($winsSolo + $stats->getInt('losses_solo')) > 0) {
                    $winsPercentageSolo = round(($winsSolo / ($winsSolo + $stats->getInt('losses_solo'))) * 100, 1);
                } else {
                    $winsPercentageSolo = 0;
                }

                $killsTeam = $stats->getInt('kills_team');
                if ($stats->getInt('deaths_team') > 0) {
                    $kdTeams = round($killsTeam / $stats->getInt('deaths_team'), 2);
                } else {
                    $kdTeams = 'N/A';
                }

                $winsTeam = $stats->getInt('wins_team');
                if (($winsTeam + $stats->getInt('losses_team')) > 0) {
                    $winsPercentageTeams = round(($winsTeam / ($winsTeam + $stats->getInt('losses_team'))) * 100, 1);
                } else {
                    $winsPercentageTeams = 0;
                }

                $killsMega = $stats->getInt('kills_mega');
                if ($stats->getInt('deaths_mega') > 0) {
                    $kdMega = round($killsMega / $stats->getInt('deaths_mega'), 2);
                } else {
                    $kdMega = 'N/A';
                }

                $winsMega = $stats->getInt('wins_mega');
                if (($winsMega + $stats->getInt('losses_mega')) > 0) {
                    $winsPercentageMega = round(($winsMega / ($winsMega + $stats->getInt('losses_mega'))) * 100, 1);
                } else {
                    $winsPercentageMega = 0;
                }

                return [
                    'wins'            => $wins,
                    'kills'           => $kills,
                    'kd'              => $kd,
                    'wins_percentage' => $winsPercentage,

                    'wins_solo'            => $winsSolo,
                    'kills_solo'           => $killsSolo,
                    'kd_solo'              => $kdSolo,
                    'wins_percentage_solo' => $winsPercentageSolo,

                    'wins_teams'            => $winsTeam,
                    'kills_teams'           => $killsTeam,
                    'kd_teams'              => $kdTeams,
                    'wins_percentage_teams' => $winsPercentageTeams,

                    'wins_mega'            => $winsMega,
                    'kills_mega'           => $killsMega,
                    'kd_mega'              => $kdMega,
                    'wins_percentage_mega' => $winsPercentageMega,
                ];
            });
        }
    }
