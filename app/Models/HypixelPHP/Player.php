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

namespace App\Models\HypixelPHP;

    use Eloquent;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Carbon;
    use JsonException;

    /**
     * Class Player
     *
     * @package App\Models\HypixelPHP
     * @method static Builder|Player newModelQuery()
     * @method static Builder|Player newQuery()
     * @method static Builder|Player query()
     * @mixin Eloquent
     * @property string      $uuid
     * @property string|null $data
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @method static Builder|Player whereCreatedAt($value)
     * @method static Builder|Player whereData($value)
     * @method static Builder|Player whereUpdatedAt($value)
     * @method static Builder|Player whereUuid($value)
     */
    class Player extends Model {
        protected $table = 'hypixel_players';

        protected $fillable = ['uuid', 'data'];

        protected $primaryKey = 'uuid';
        protected $keyType = 'uuid';

        /**
         * @param $jsonData
         *
         * @return array
         * @throws JsonException
         */
        public function getDataAttribute($jsonData): array {
            return json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        }
    }
