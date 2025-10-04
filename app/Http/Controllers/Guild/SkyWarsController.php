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

    namespace App\Http\Controllers\Guild;

    use App\Exceptions\HypixelFetchException;
    use App\Utilities\HypixelAPI;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Http\Request;
    use Illuminate\View\View;
    use Plancke\HypixelPHP\classes\serverType\ServerTypes;
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
                if (empty($guild->getData())) {
                    return redirect()->route('guild')->withErrors(['username' => 'This guild does not exist']);
                }

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
         *
         * @return array[]
         * @throws HypixelPHPException
         */
        private function getSkyWarsMemberList(Guild $guild): array {
            return $this->getMemberList($guild, static function (Player $player) {
                /** @var GameStats $stats */
                $stats = $player->getStats()->getGameFromID(ServerTypes::SKYWARS);

                $kills  = $stats->getInt('kills');
                $deaths = $stats->getInt('deaths');
                $kd = $deaths > 0 ? round($kills / $deaths, 2) : 'N/A';

                $wins   = $stats->getInt('wins');
                $losses = $stats->getInt('losses');
                $winsPercentage = $wins + $losses > 0 ? round(($wins / ($wins + $losses)) * 100, 1) : 0;

                $killsSolo  = $stats->getInt('kills_solo');
                $deathsSolo = $stats->getInt('deaths_solo');
                $kdSolo = $deathsSolo > 0 ? round($killsSolo / $deathsSolo, 2) : 'N/A';

                $winsSolo   = $stats->getInt('wins_solo');
                $lossesSolo = $stats->getInt('losses_solo');
                $winsPercentageSolo = $winsSolo + $lossesSolo > 0 ? round(($winsSolo / ($winsSolo + $lossesSolo)) * 100, 1) : 0;

                $killsTeam  = $stats->getInt('kills_team');
                $deathsTeam = $stats->getInt('deaths_team');
                $kdTeams = $deathsTeam > 0 ? round($killsTeam / $deathsTeam, 2) : 'N/A';

                $winsTeam   = $stats->getInt('wins_team');
                $lossesTeam = $stats->getInt('losses_team');
                $winsPercentageTeams = $winsTeam + $lossesTeam > 0 ? round(($winsTeam / ($winsTeam + $lossesTeam)) * 100, 1) : 0;

                $killsMega  = $stats->getInt('kills_mega');
                $deathsMega = $stats->getInt('deaths_mega');
                $kdMega = $deathsMega > 0 ? round($killsMega / $deathsMega, 2) : 'N/A';

                $winsMega   = $stats->getInt('wins_mega');
                $lossesMega = $stats->getInt('losses_mega');
                $winsPercentageMega = $winsMega + $lossesMega > 0 ? round(($winsMega / ($winsMega + $lossesMega)) * 100, 1) : 0;

                return [
                    'wins'            => $wins,
                    'losses'          => $losses,
                    'kills'           => $kills,
                    'deaths'          => $deaths,
                    'kd'              => $kd,
                    'wins_percentage' => $winsPercentage,

                    'wins_solo'            => $winsSolo,
                    'losses_solo'          => $lossesSolo,
                    'kills_solo'           => $killsSolo,
                    'deaths_solo'          => $deathsSolo,
                    'kd_solo'              => $kdSolo,
                    'wins_percentage_solo' => $winsPercentageSolo,

                    'wins_teams'            => $winsTeam,
                    'losses_teams'          => $lossesTeam,
                    'kills_teams'           => $killsTeam,
                    'deaths_teams'          => $deathsTeam,
                    'kd_teams'              => $kdTeams,
                    'wins_percentage_teams' => $winsPercentageTeams,

                    'wins_mega'            => $winsMega,
                    'losses_mega'          => $lossesMega,
                    'kills_mega'           => $killsMega,
                    'deaths_mega'          => $deathsMega,
                    'kd_mega'              => $kdMega,
                    'wins_percentage_mega' => $winsPercentageMega,
                ];
            });
        }
    }
