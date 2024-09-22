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

    namespace App\Utilities;

    use Illuminate\Support\Facades\Request;
    use Illuminate\Support\Str;
    use Log;
    use Plancke\HypixelPHP\log\Logger as HypixelPHPLogger;

    /**
     * Class LaravelLogger
     *
     * @package App\Utilities
     */
    class LaravelLogger extends HypixelPHPLogger {
        protected array $levels = [
            LOG_DEBUG   => 'debug',
            LOG_INFO    => 'info',
            LOG_NOTICE  => 'notice',
            LOG_WARNING => 'warning',
            LOG_ERR     => 'error',
            LOG_CRIT    => 'critical',
            LOG_ALERT   => 'alert',
            LOG_EMERG   => 'emergency'
        ];

        /**
         * @param int    $level
         * @param string $line
         */
        protected function actuallyLog($level, $line): void {
            if (Str::contains($line, ['vendor/plancke/hypixel-php', '(200/429)', 'Operation timed out after'])) {
                if (!Str::containsAll($line, ['DefaultFetcher.php', '(200/429)'])) {
                    Log::stack(['daily'])->log($this->levels[$level], $line, ['url' => Request::url()]);
                }
            } else {
                Log::log($this->levels[$level], $line);
            }
        }
    }
