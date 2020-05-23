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

    namespace App\Console\Commands;

    use Cache;
    use File;
    use Illuminate\Console\Command;
    use Illuminate\Http\Client\RequestException;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Http;
    use Symfony\Component\Process\Exception\ProcessFailedException;
    use Symfony\Component\Process\Process;

    /**
     * Class GetSkyBlockConstants
     *
     * @package App\Console\Commands
     */
    class GetSkyBlockConstants extends Command {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'skyblock:get-constants';
        protected $constantsUrl = 'https://github.com/LeaPhant/skyblock-stats/raw/master/src/constants.js';
        protected $licenseUrl = 'https://github.com/LeaPhant/skyblock-stats/raw/master/LICENSE';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Downloads and parses the constants used in various SkyBlock calculations from the skyblock-stats GitHub repository';

        /**
         * Execute the console command.
         *
         * @return int
         * @throws RequestException
         */
        public function handle(): int {
            $this->info('Now attempting to download SkyBlock constants from ' . $this->constantsUrl);
            $licenseResponse = Http::get($this->licenseUrl);
            if (!$licenseResponse->successful()) {
                $this->error('Could not download license information from ' . $this->licenseUrl . ', aborting');
                return 1;
            }

            $this->info('License information for the source code that is being obtained:');
            $this->line($licenseResponse->body());

            File::put(resource_path('data/skyblock/LICENSE'), $licenseResponse->body());

            $response = Http::get($this->constantsUrl);
            if (!$response->successful()) {
                $this->error('Could not download SkyBlock constants from GitHub');
                $response->throw();
                return 1;
            }

            $constantsRaw = $response->body();

            File::put(resource_path('data/skyblock/constants.js'), $constantsRaw);

            $this->info('Parsing source code and converting it to JSON. This requires Node.js to be installed on your system.');

            $process = new Process(['node', resource_path('data/skyblock/ParseJavaScriptObjectToJSON.js')]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $constants = new Collection(json_decode($process->getOutput(), false));

            File::put(resource_path('data/skyblock/constants.json'), $constants->only([
                'leveling_xp',
                'runecrafting_xp',
                'bonus_stats',
                'stat_template',
                'base_stats',
                'slayer_xp',
                'slayer_cost',
                'pet_rarity_offset',
                'pet_levels',
                'pet_data',
                'pet_items',
                'pet_value',
                'pet_rewards',
                'item_types',
                'talisman_upgrades',
                'talisman_duplicates',
            ])->toJson());
            File::delete(resource_path('data/skyblock/constants.js'));

            $this->info('Successfully downloaded and parsed constants.js to constants.json');

            Cache::pull('skyblock.constants');
            return 0;
        }

    }
