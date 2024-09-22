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

    use Cache;
    use File;
    use Illuminate\Console\Command;
    use Illuminate\Contracts\Filesystem\FileNotFoundException;
    use Illuminate\Http\Client\RequestException;
    use Illuminate\Support\Collection;
    use JsonException;
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
        protected string $constantsPath;
        protected string $licensePath;

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Parses the constants used in various SkyBlock calculations from the SkyCrypt GitHub repository';

        public function __construct() {
            $this->licensePath   = resource_path('data/skyblock/SkyCrypt/LICENSE');
            $this->constantsPath = resource_path('data/skyblock/SkyCrypt/src/constants');

            parent::__construct();
        }

        /**
         * Execute the console command.
         *
         * @return int
         * @throws RequestException|JsonException
         * @throws FileNotFoundException
         */
        public function handle(): int {
            if (!File::exists($this->licensePath)) {
                $this->error('Could not copy license information from ' . $this->licensePath . ', aborting. Did you initialize the git submodules yet?');

                return 1;
            }

            File::copy($this->licensePath, resource_path('data/skyblock/LICENSE'));

            $licenseResponse = File::get($this->licensePath);

            $this->info('License information for the source code that is being obtained:');
            $this->line($licenseResponse);

            if (!File::isDirectory($this->constantsPath)) {
                $this->error($this->constantsPath . ' is not a directory, aborting');

                return 1;
            }

            $this->info('Parsing source code and converting it to JSON. This requires Node.js to be installed on your system.');

            $process = new Process(['node', resource_path('data/skyblock/ParseJavaScriptObjectToJSON.js')]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $constants = new Collection(json_decode($process->getOutput(), false, 512, JSON_THROW_ON_ERROR));

            File::put(resource_path('data/skyblock/constants.json'), $constants->toJson());

            File::delete(resource_path('data/skyblock/constants.js'));

            $this->info('Successfully downloaded and parsed constants.js to constants.json');

            Cache::pull('skyblock.constants');

            return 0;
        }

    }
