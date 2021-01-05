<?php
    /*
 * Copyright (c) 2021 Max Korlaar
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

    namespace App\Jobs\Friends;

    use App\Utilities\HypixelAPI;
    use Cache;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\Redis\LimiterTimeoutException;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Redis;
    use Log;
    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\color\ColorUtils;

    /**
     * Class LoadPlayerData
     *
     * @package App\Jobs\Friends
     */
    class LoadPlayerData implements ShouldQueue {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        private string $uuid;

        /**
         * Create a new job instance.
         *
         * @param $uuid
         */
        public function __construct(string $uuid) {
            $this->queue = 'hypixel-api';
            $this->uuid  = $uuid;
        }

        /**
         * Execute the job.
         *
         * @return void
         * @throws LimiterTimeoutException
         */
        public function handle(): void {
            Redis::throttle('hypixel.friends.player_data')->allow(400)->every(60)->block(0)->then(function () {
                $api = new HypixelAPI();

                $player = $api->getPlayerByUuid($this->uuid);

                /** @var HypixelObject $player */
                if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                    Log::error('Bad API response in LoadPlayerData', [$player->getResponse()->getData()]);

                    if ($this->attempts() > 1) {
                        return $this->fail('Bad API response in LoadPlayerData');
                    }

                    return $this->release(30);
                }

                if ($player !== null) {
                    Cache::set('hypixel.player.' . $this->uuid, [
                        'username'              => $player->getName(),
                        'formatted_name'        => ColorUtils::getColorParser()->parse($player->getRawFormattedName()),
                        'loading'               => false,
                        'last_login'            => $player->getInt('lastLogin', null),
                        'most_recent_game_type' => $player->get('mostRecentGameType', null)
                    ], config('cache.times.friends_profiles'));
                }

                return null;
            }, static function () {
                // Could not obtain lock...
            });
        }
    }
