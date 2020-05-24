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

    /**
     * Created by Max in 2020
     */

    namespace App\Utilities\HypixelPHP;

    use App\Models\HypixelPHP\Player as PlayerModel;
    use JsonException;
    use Plancke\HypixelPHP\cache\CacheHandler;
    use Plancke\HypixelPHP\responses\booster\Boosters;
    use Plancke\HypixelPHP\responses\friend\Friends;
    use Plancke\HypixelPHP\responses\gameCounts\GameCounts;
    use Plancke\HypixelPHP\responses\guild\Guild;
    use Plancke\HypixelPHP\responses\KeyInfo;
    use Plancke\HypixelPHP\responses\Leaderboards;
    use Plancke\HypixelPHP\responses\player\Player;
    use Plancke\HypixelPHP\responses\PlayerCount;
    use Plancke\HypixelPHP\responses\RecentGames;
    use Plancke\HypixelPHP\responses\skyblock\SkyBlockProfile;
    use Plancke\HypixelPHP\responses\Status;
    use Plancke\HypixelPHP\responses\WatchdogStats;

    /**
     * Class DatabaseCacheHandler
     *
     * @package App\Utilities\HypixelPHP
     */
    class DatabaseCacheHandler extends CacheHandler {

        /**
         * @param $resource
         *
         * @return Resource
         */
        public function getResource($resource) {
            // TODO: Implement getResource() method.
        }

        /**
         * @param Resource $resource
         *
         * @return void
         */
        public function setResource($resource) {
            // TODO: Implement setResource() method.
        }

        /**
         * @param $uuid
         *
         * @return Player|null
         */
        public function getPlayer($uuid): ?Player {
            return $this->wrapProvider(
                $this->getHypixelPHP()->getProvider()->getPlayer(),
                $data = optional(PlayerModel::find($uuid))->data
            );
        }

        /**
         * @param Player $player
         *
         * @return void
         * @throws JsonException
         */
        public function setPlayer(Player $player): void {
            PlayerModel::updateOrCreate([
                'uuid' => $player->getUUID(),
                'data' => json_encode($this->objToArray($player), JSON_THROW_ON_ERROR)
            ]);
        }

        /**
         * @param $username
         *
         * @return string|null
         */
        public function getUUID($username) {
            // TODO: Implement getUUID() method.
        }

        /**
         * @param $username
         * @param $uuid
         *
         * @return void
         */
        public function setPlayerUUID($username, $uuid) {
            // TODO: Implement setPlayerUUID() method.
        }

        /**
         * @param $id
         *
         * @return Guild|null
         */
        public function getGuild($id) {
            // TODO: Implement getGuild() method.
        }

        /**
         * @param Guild $guild
         *
         * @return void
         */
        public function setGuild(Guild $guild) {
            // TODO: Implement setGuild() method.
        }

        /**
         * @param $uuid
         *
         * @return Guild|string|null
         */
        public function getGuildIDForUUID($uuid) {
            // TODO: Implement getGuildIDForUUID() method.
        }

        /**
         * @param $uuid
         * @param $id
         *
         * @return void
         */
        public function setGuildIDForUUID($uuid, $id) {
            // TODO: Implement setGuildIDForUUID() method.
        }

        /**
         * @param $name
         *
         * @return Guild|string|null
         */
        public function getGuildIDForName($name) {
            // TODO: Implement getGuildIDForName() method.
        }

        /**
         * @param $name
         * @param $id
         *
         * @return void
         */
        public function setGuildIDForName($name, $id) {
            // TODO: Implement setGuildIDForName() method.
        }

        /**
         * @param $uuid
         *
         * @return Friends|null
         */
        public function getFriends($uuid) {
            // TODO: Implement getFriends() method.
        }

        /**
         * @param Friends $friends
         *
         * @return void
         */
        public function setFriends(Friends $friends) {
            // TODO: Implement setFriends() method.
        }

        /**
         * @param $uuid
         *
         * @return Status|null
         */
        public function getStatus($uuid) {
            // TODO: Implement getStatus() method.
        }

        /**
         * @param Status $status
         *
         * @return void
         */
        public function setStatus(Status $status) {
            // TODO: Implement setStatus() method.
        }

        /**
         * @param $uuid
         *
         * @return RecentGames|null
         */
        public function getRecentGames($uuid) {
            // TODO: Implement getRecentGames() method.
        }

        /**
         * @param RecentGames $recentGames
         *
         * @return void
         */
        public function setRecentGames(RecentGames $recentGames) {
            // TODO: Implement setRecentGames() method.
        }

        /**
         * @param $key
         *
         * @return KeyInfo|null
         */
        public function getKeyInfo($key) {
            // TODO: Implement getKeyInfo() method.
        }

        /**
         * @param KeyInfo $keyInfo
         *
         * @return void
         */
        public function setKeyInfo(KeyInfo $keyInfo) {
            // TODO: Implement setKeyInfo() method.
        }

        /**
         * @return Leaderboards|null
         */
        public function getLeaderboards() {
            // TODO: Implement getLeaderboards() method.
        }

        /**
         * @param Leaderboards $leaderboards
         *
         * @return void
         */
        public function setLeaderboards(Leaderboards $leaderboards) {
            // TODO: Implement setLeaderboards() method.
        }

        /**
         * @return Boosters|null
         */
        public function getBoosters() {
            // TODO: Implement getBoosters() method.
        }

        /**
         * @param Boosters $boosters
         *
         * @return void
         */
        public function setBoosters(Boosters $boosters) {
            // TODO: Implement setBoosters() method.
        }

        /**
         * @return WatchdogStats|null
         */
        public function getWatchdogStats() {
            // TODO: Implement getWatchdogStats() method.
        }

        /**
         * @param WatchdogStats $watchdogStats
         *
         * @return void
         */
        public function setWatchdogStats(WatchdogStats $watchdogStats) {
            // TODO: Implement setWatchdogStats() method.
        }

        /**
         * @return PlayerCount|null
         */
        public function getPlayerCount() {
            // TODO: Implement getPlayerCount() method.
        }

        /**
         * @param PlayerCount $playerCount
         *
         * @return void
         */
        public function setPlayerCount(PlayerCount $playerCount) {
            // TODO: Implement setPlayerCount() method.
        }

        /**
         * @return GameCounts|null
         */
        public function getGameCounts() {
            // TODO: Implement getGameCounts() method.
        }

        /**
         * @param GameCounts $gameCounts
         *
         * @return void
         */
        public function setGameCounts(GameCounts $gameCounts) {
            // TODO: Implement setGameCounts() method.
        }

        /**
         * @param $profile_id
         *
         * @return SkyBlockProfile|null
         */
        public function getSkyBlockProfile($profile_id) {
            // TODO: Implement getSkyBlockProfile() method.
        }

        /**
         * @param SkyBlockProfile $profile
         *
         * @return void
         */
        public function setSkyBlockProfile(SkyBlockProfile $profile) {
            // TODO: Implement setSkyBlockProfile() method.
        }
    }
