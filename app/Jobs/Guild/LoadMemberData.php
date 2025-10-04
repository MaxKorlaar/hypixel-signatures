<?php
/*
 * Copyright (c) 2021-2025 Max Korlaar
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

namespace App\Jobs\Guild;

    use App\Utilities\HypixelAPI;
    use Cache;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\Redis\LimiterTimeoutException;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\Middleware\WithoutOverlapping;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Redis;
    use Log;
    use Plancke\HypixelPHP\cache\CacheTimes;
    use Plancke\HypixelPHP\classes\HypixelObject;

    /**
     * Class LoadMemberData
     *
     * @package App\Jobs\Guild
     */
    class LoadMemberData implements ShouldQueue, ShouldBeUniqueUntilProcessing {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        /**
         * The number of times the job may be attempted.
         *
         * @var int
         */
        public int $tries = 1;

        /**
         * Create a new job instance.
         */
        public function __construct(private string $uuid) {
            $this->queue = 'hypixel-api';
        }

        /**
         * Execute the job.
         *
         * @throws LimiterTimeoutException
         */
        public function handle(): void {
            Redis::throttle('hypixel.guild.player_data')->allow(1000)->every(60)->block(0)->then(function () {
                $api = new HypixelAPI();

                $player = $api->getPlayerByUuid($this->uuid);

                /** @var HypixelObject $player */
                if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                    Log::error('Bad API response in LoadPlayerData', [$player->getResponse()->getData()]);

                    return $this->release(30);
                }

                if ($player !== null) {
                    Cache::set('hypixel.players_loaded. ' . $this->uuid, true, $api->getApi()->getCacheHandler()->getCacheTime(CacheTimes::PLAYER));
                }

                return null;
            }, static function () {
                // Could not obtain lock...
            });
        }

        public function uniqueId(): string {
            return $this->uuid;
        }

        public function middleware(): array {
            return [
                new WithoutOverlapping($this->uuid)
                    ->expireAfter(60)
                    ->dontRelease(),
            ];
        }
    }
