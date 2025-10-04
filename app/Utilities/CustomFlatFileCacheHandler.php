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

use Plancke\HypixelPHP\cache\impl\FlatFileCacheHandler;
use Plancke\HypixelPHP\responses\guild\Guild;

/**
 * Custom cache handler that extends FlatFileCacheHandler to properly implement
 * guild lookup methods that were throwing exceptions in the base implementation.
 *
 * Class CustomFlatFileCacheHandler
 * @package App\Utilities
 */
class CustomFlatFileCacheHandler extends FlatFileCacheHandler {

    /**
     * Get guild by player UUID from cache.
     * Uses the existing getGuildIDForUUID helper to find the guild ID,
     * then retrieves the full guild object.
     *
     * @param string $uuid
     * @return Guild|null
     */
    public function getGuildByPlayer($uuid): ?Guild {
        $guildId = $this->getGuildIDForUUID($uuid);
        if ($guildId !== null) {
            return $this->getGuild($guildId);
        }
        return null;
    }

    /**
     * Get guild by name from cache.
     * Uses the existing getGuildIDForName helper to find the guild ID,
     * then retrieves the full guild object.
     *
     * @param string $name
     * @return Guild|null
     */
    public function getGuildByName($name): ?Guild {
        $guildId = $this->getGuildIDForName($name);
        if ($guildId !== null) {
            return $this->getGuild($guildId);
        }
        return null;
    }
}
