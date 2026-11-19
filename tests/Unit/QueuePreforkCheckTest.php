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

    namespace Tests\Unit;

    use App\Utilities\QueuePreforkCheck;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\Redis;
    use Laravel\Horizon\MasterSupervisor;
    use Spatie\Health\Enums\Status;
    use Tests\TestCase;

    /**
     * Runs QueuePreforkCheck against the real `queue-prefork:status` command, with the snapshot that
     * Horizon's prefork master would publish written to Redis by hand.
     *
     * @package Tests\Unit
     */
    class QueuePreforkCheckTest extends TestCase {
        #[\Override]
        protected function setUp(): void {
            parent::setUp();

            $this->forgetSnapshot();
            Artisan::call('queue-prefork:switch', ['action' => 'clear']);
        }

        #[\Override]
        protected function tearDown(): void {
            $this->forgetSnapshot();

            parent::tearDown();
        }

        public function testPassesWhenEverySupervisorIsForked(): void {
            $this->publishSnapshot('prefork', units: [
                ['supervisor' => 'host:supervisor-1', 'kind' => 'forked', 'state' => 'running'],
            ]);

            $result = QueuePreforkCheck::new()->run();

            $this->assertTrue($result->status->equals(Status::ok()), $result->getNotificationMessage());
            $this->assertSame('prefork', $result->meta['mode']);
            $this->assertSame(0, $result->meta['stock_units']);
        }

        public function testFailsWhenHorizonPublishedNoSnapshot(): void {
            $result = QueuePreforkCheck::new()->run();

            $this->assertTrue($result->status->equals(Status::failed()));
            $this->assertSame('No snapshot', $result->getShortSummary());
            $this->assertStringContainsString('horizon:terminate', $result->getNotificationMessage());
        }

        public function testFailsWithTheReasonWhenHorizonRunsStock(): void {
            $this->publishSnapshot('stock', reason: 'Horizon 5.99.0 is newer than the tested 5.50');

            $result = QueuePreforkCheck::new()->run();

            $this->assertTrue($result->status->equals(Status::failed()));
            $this->assertSame('Stock', $result->getShortSummary());
            $this->assertStringContainsString('Horizon 5.99.0 is newer than the tested 5.50', $result->getNotificationMessage());
        }

        public function testFailsWhenEverySupervisorFellBackToStock(): void {
            $this->publishSnapshot('degraded', units: [
                ['supervisor' => 'host:supervisor-1', 'kind' => 'stock', 'reason' => 'crash breaker'],
            ]);

            $result = QueuePreforkCheck::new()->run();

            $this->assertTrue($result->status->equals(Status::failed()));
            $this->assertSame('Degraded', $result->getShortSummary());
        }

        public function testFailsWhenOneSupervisorRunsStock(): void {
            $this->publishSnapshot('prefork', units: [
                ['supervisor' => 'host:supervisor-1', 'kind' => 'forked', 'state' => 'running'],
                ['supervisor' => 'host:supervisor-2', 'kind' => 'stock', 'reason' => 'crash breaker'],
            ]);

            $result = QueuePreforkCheck::new()->run();

            $this->assertTrue($result->status->equals(Status::failed()));
            $this->assertSame('Partly stock', $result->getShortSummary());
            $this->assertStringContainsString('host:supervisor-2 (crash breaker)', $result->getNotificationMessage());
            $this->assertSame(1, $result->meta['stock_units']);
        }

        public function testWarnsWhenPreforkIsSwitchedOff(): void {
            Artisan::call('queue-prefork:switch', ['action' => 'disable']);
            $this->publishSnapshot('stock', reason: 'disabled');

            try {
                $result = QueuePreforkCheck::new()->run();
            } finally {
                Artisan::call('queue-prefork:switch', ['action' => 'clear']);
            }

            $this->assertTrue($result->status->equals(Status::warning()));
            $this->assertSame('Disabled', $result->getShortSummary());
            $this->assertStringContainsString('switch', $result->getNotificationMessage());
        }

        public function testFailsWhenTheStatusOutputCannotBeRead(): void {
            Artisan::shouldReceive('call')->once()->with('queue-prefork:status', ['--json' => true])->andReturn(1);
            Artisan::shouldReceive('output')->once()->andReturn('not json');

            $result = QueuePreforkCheck::new()->run();

            $this->assertTrue($result->status->equals(Status::failed()));
            $this->assertSame('Unknown', $result->getShortSummary());
        }

        /**
         * @param list<array<string, string>> $units
         */
        private function publishSnapshot(string $mode, ?string $reason = null, array $units = []): void {
            Redis::connection('horizon')->setex($this->snapshotKey(), 30, json_encode([
                'at'     => time(),
                'master' => 1,
                'mode'   => $mode,
                'reason' => $reason,
                'units'  => $units,
            ]));
        }

        private function forgetSnapshot(): void {
            Redis::connection('horizon')->del($this->snapshotKey());
        }

        private function snapshotKey(): string {
            return 'prefork:snapshot:' . MasterSupervisor::basename();
        }
    }
