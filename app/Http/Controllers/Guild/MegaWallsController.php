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
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\View\View;
    use Plancke\HypixelPHP\classes\gameType\GameTypes;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\guild\Guild;
    use Plancke\HypixelPHP\responses\player\GameStats;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class MegaWallsController
     *
     * @package App\Http\Controllers\Guild
     */
    class MegaWallsController extends MemberController {
        /**
         * @param Request $request
         * @param string  $nameOrId
         *
         * @return array[]|Application|Factory|RedirectResponse|View
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         */
        public function getMegaWallsStatistics(Request $request, string $nameOrId) {
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

                $memberList = $this->getMegaWallsMemberList($guild);

                if ($request->wantsJson()) {
                    return $memberList;
                }

                return view('guild.games.megawalls', [
                        'guild' => $guild,
                        'urls'  => [
                            'get_members' => route('guild.games.megawalls.json', [$guild->getID()])
                        ]
                    ] + $memberList);
            }

            throw new HypixelFetchException('An unknown error has occurred while trying to fetch this Guild or its members from Hypixel');
        }

        /**
         * @param Guild $guild
         *
         * @return array
         * @throws HypixelPHPException
         */
        private function getMegaWallsMemberList(Guild $guild): array {
            return $this->getMemberList($guild, static function (Player $player) {
                /** @var GameStats $stats */
                $stats = $player->getStats()->getGameFromID(GameTypes::WALLS3);

                $kills  = $stats->getInt('kills');
                $deaths = $stats->getInt('deaths');

                if ($deaths > 0) {
                    $kd = round($kills / $deaths, 2);
                } else {
                    $kd = 'N/A';
                }

                $wins   = $stats->getInt('wins');
                $losses = $stats->getInt('losses');
                if (($wins + $losses) > 0) {
                    $winsPercentage = round(($wins / ($wins + $losses)) * 100, 1);
                } else {
                    $winsPercentage = 0;
                }

                $finalKills  = $stats->getInt('final_kills');
                $finalDeaths = $stats->getInt('finalDeaths') + $stats->getInt('final_deaths');

                if ($finalDeaths > 0) {
                    $finalKd = round($finalKills / $finalDeaths, 2);
                } else {
                    $finalKd = 'N/A';
                }

                return [
                    'wins'            => $wins,
                    'losses'          => $losses,
                    'kills'           => $kills,
                    'deaths'          => $deaths,
                    'assists'         => $stats->getInt('assists'),
                    'wins_percentage' => $winsPercentage,
                    'kd'              => $kd,
                    'kills_final'     => $finalKills,
                    'deaths_final'    => $finalDeaths,
                    'kd_final'        => $finalKd
                ];
            });
        }
    }
