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
     * Class MurderMysteryController
     *
     * @package App\Http\Controllers\Guild
     */
    class MurderMysteryController extends MemberController {
        /**
         *
         * @return array[]|Application|Factory|RedirectResponse|View
         * @throws HypixelFetchException
         * @throws HypixelPHPException
         */
        public function getMurderMysteryStatistics(Request $request, string $nameOrId) {
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

                $memberList = $this->getMurderMysteryMemberList($guild);

                if ($request->wantsJson()) {
                    return $memberList;
                }

                return view('guild.games.murdermystery', [
                        'guild' => $guild,
                        'urls'  => [
                            'get_members' => route('guild.games.murdermystery.json', [$guild->getID()])
                        ]
                    ] + $memberList);
            }

            throw new HypixelFetchException('An unknown error has occurred while trying to fetch this Guild or its members from Hypixel');
        }

        /**
         * @throws HypixelPHPException
         */
        private function getMurderMysteryMemberList(Guild $guild): array {
            return $this->getMemberList($guild, static function (Player $player) {
                /** @var GameStats $stats */
                $stats = $player->getStats()->getGameFromID(GameTypes::MURDER_MYSTERY);

                $wins        = $stats->getInt('wins');
                $kills       = $stats->getInt('kills');
                $deaths      = $stats->getInt('deaths');
                $gamesPlayed = $stats->getInt('games');

                $quickestDetectiveWinTime = $stats->getInt('quickest_detective_win_time_seconds');
                $quickestMurdererWinTime  = $stats->getInt('quickest_murderer_win_time_seconds');

                $detectiveWins = $stats->getInt('detective_wins');
                $murdererWins  = $stats->getInt('murderer_wins');

                $winsPercentage = $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 1) : 0;

                $kd = $deaths > 0 ? round($kills / $deaths, 2) : 'N/A';

                return [
                    'wins'                        => $wins,
                    'kills'                       => $kills,
                    'deaths'                      => $deaths,
                    'quickest_detective_win_time' => $quickestDetectiveWinTime,
                    'quickest_murderer_win_time'  => $quickestMurdererWinTime,
                    'kd'                          => $kd,
                    'wins_percentage'             => $winsPercentage,
                    'games'                       => $gamesPlayed,
                    'wins_detective'              => $detectiveWins,
                    'wins_murderer'               => $murdererWins
                ];
            });
        }
    }
