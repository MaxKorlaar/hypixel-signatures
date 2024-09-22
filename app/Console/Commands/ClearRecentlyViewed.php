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

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Redis;

    /**
     * Class ClearRecentlyViewed
     *
     * @package App\Console\Commands
     */
    class ClearRecentlyViewed extends Command {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'hypixel-cache:clear-recent';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Clear list of recently viewed players and guilds by site visitors';

        /**
         * Execute the console command.
         *
         * @return mixed
         */
        public function handle(): void {
            $recentFriendsCount = Redis::connection('cache')->zCount('recent_friends', '-inf', '+inf');

            $this->info('recent_friends size: ' . $recentFriendsCount);

            Redis::connection('cache')->zPopMin('recent_friends', max(0, $recentFriendsCount - 20));
            Redis::connection('cache')->expire('recent_friends', config('cache.times.recent_players'));

            $recentGuildsCount = Redis::connection('cache')->zCount('recent_guilds', '-inf', '+inf');

            $this->info('recent_guilds size: ' . $recentGuildsCount);

            Redis::connection('cache')->zPopMin('recent_guilds', max(0, $recentGuildsCount - 20));
            Redis::connection('cache')->expire('recent_guilds', config('cache.times.recent_guilds'));

            $recentOnlinePlayersCount = Redis::connection('cache')->zCount('recent_online_players', '-inf', '+inf');

            $this->info('recent_online_players size: ' . $recentOnlinePlayersCount);

            Redis::connection('cache')->zPopMin('recent_online_players', max(0, $recentOnlinePlayersCount - 20));
            Redis::connection('cache')->expire('recent_online_players', config('cache.times.recent_players'));

            foreach (config('recents.blocklist.players') as $uuid) {
                Redis::connection('cache')->zRem('recent_online_players', $uuid);
            }

            $this->info('Cleared recently viewed players');
        }
    }
