<?php
    /*
     * Copyright (c) 2026 Max Korlaar
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

    use Illuminate\Support\Facades\Artisan;
    use Spatie\Health\Checks\Check;
    use Spatie\Health\Checks\Result;

    /**
     * Fails when Horizon's workers are not forked by tevkert/queue-prefork, so a silent fallback to
     * stock workers (and their full memory use) is noticed. Reads `queue-prefork:status`, which takes
     * the Horizon mode from the snapshot the master publishes to Redis every few seconds.
     *
     * @package App\Utilities
     */
    class QueuePreforkCheck extends Check {
        public function run(): Result {
            $result = Result::make();

            Artisan::call('queue-prefork:status', ['--json' => true]);

            /** @var array{enabled?: bool, source?: string, snapshot?: array{mode?: string, reason?: string|null, units?: list<array{supervisor?: string, kind?: string, reason?: string}>}|null, processes?: list<array<string, mixed>>, total_pss_mb?: float|int}|null $status */
            $status = json_decode(Artisan::output(), true);

            if (!is_array($status)) {
                return $result->shortSummary('Unknown')->failed('Could not read the output of queue-prefork:status.');
            }

            $snapshot = $status['snapshot'] ?? null;
            $mode = $snapshot['mode'] ?? null;
            $stockUnits = array_values(array_filter(
                $snapshot['units'] ?? [],
                static fn (array $unit): bool => ($unit['kind'] ?? '') === 'stock'
            ));
            $totalPssMb = $status['total_pss_mb'] ?? 0;

            $result->meta([
                'mode'         => $mode,
                'stock_units'  => count($stockUnits),
                'processes'    => count($status['processes'] ?? []),
                'total_pss_mb' => $totalPssMb,
            ]);

            if (!($status['enabled'] ?? false)) {
                return $result->shortSummary('Disabled')->warning(
                    'Prefork is turned off (by ' . ($status['source'] ?? 'config') . '), so Horizon runs stock workers.'
                );
            }

            if ($snapshot === null) {
                return $result->shortSummary('No snapshot')->failed(
                    'Horizon has published no prefork snapshot in the last 30 seconds: it is down, or still runs the code from before queue-prefork was installed (run `php artisan horizon:terminate`).'
                );
            }

            if ($mode !== 'prefork') {
                return $result->shortSummary(ucfirst((string) $mode))->failed(
                    "Horizon runs {$mode}: " . ($snapshot['reason'] ?? 'no reason given') . '. Run `php artisan queue-prefork:doctor` as the Horizon user.'
                );
            }

            if ($stockUnits !== []) {
                $names = implode(', ', array_map(
                    static fn (array $unit): string => ($unit['supervisor'] ?? '?') . ' (' . ($unit['reason'] ?? 'no reason given') . ')',
                    $stockUnits
                ));

                return $result->shortSummary('Partly stock')->failed("These supervisors run stock: {$names}.");
            }

            return $result->shortSummary($totalPssMb > 0 ? "Prefork, {$totalPssMb} MB" : 'Prefork')->ok();
        }
    }
