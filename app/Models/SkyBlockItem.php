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

    namespace App\Models;

    use App\Utilities\SkyBlock\SkyBlockStatsDataParser;
    use ArrayAccess;
    use Illuminate\Contracts\Support\Arrayable;
    use Illuminate\Contracts\Support\Jsonable;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    use JsonSerializable;
    use pocketmine\nbt\BigEndianNbtSerializer;
    use pocketmine\nbt\tag\CompoundTag;
    use pocketmine\nbt\tag\ListTag;
    use pocketmine\nbt\tag\Tag;
    use Ramsey\Uuid\UuidFactory;

    /**
     * Class SkyBlockItem
     *
     * @package App\Models
     */
    class SkyBlockItem implements Arrayable, ArrayAccess, Jsonable, JsonSerializable {
        private static SkyBlockStatsDataParser $dataParser;
        private static UuidFactory $uuidFactory;
        private $data;

        /**
         * SkyBlockItem constructor.
         */
        public function __construct(CompoundTag $nbtItem) {
            $this->data = $this->simplify($nbtItem);

            if ($this->hasData()) {
                $this['item_uuid'] = self::$uuidFactory->uuid4()->toString();
            }

            $this->checkIfBackpack();
            $this->parseDisplayName();
            $this->parseStats();
            $this->parseItemType();
        }

        /**
         * @return mixed|Collection
         */
        private function simplify(Tag $item) {
            if ($item instanceof CompoundTag || $item instanceof ListTag) {
                return new Collection($item->getValue())->mapWithKeys(function ($mapItem, $key) {
                    /** @var Tag $mapItem */
                    return [$key => $this->simplify($mapItem)];
                });
            }

            return $item->getValue();
        }

        public function hasData(): bool {
            return $this->data->isNotEmpty();
        }

        private function checkIfBackpack(): void {
            if (Arr::has($this, 'tag.display.Name') &&
                Str::endsWith($this['tag']['display']['Name'], ['Backpack', 'New Year Cake Bag'])) {
                // Item is a backpack

                $extraAttributes = $this['tag']['ExtraAttributes'];
                $backpackData    = null;

                foreach ($extraAttributes as $name => $attribute) {
                    if (Str::endsWith($name, ['backpack_data', 'new_year_cake_bag_data'])) {
                        $backpackData = $attribute;
                    }
                }

                if ($backpackData !== null) {
                    $this['contains_items'] = $this->getBackpackContents($backpackData);
                }
            }
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L204
         *
         *
         */
        private function getBackpackContents(string $backpackData): Collection {
            $data = gzdecode($backpackData);

            $nbtStream = new BigEndianNbtSerializer();
            $nbtData   = $nbtStream->read($data);

            $compoundTag = $nbtData->getTag();

            /** @var ListTag $items */
            $items = $compoundTag->getValue()['i'];

            $return = new Collection();

            /** @var CompoundTag $itemNbt */
            foreach ($items as $index => $itemNbt) {
                $item                = new self($itemNbt);
                $item['is_inactive'] = true;
                $item['item_index']  = $index;
                $item['in_backpack'] = true;
                $return[]            = $item;
            }

            return $return;
        }

        private function parseDisplayName(): void {
            if (Arr::has($this, 'tag.display.Name')) {
                $this['display_name'] = $this->cleanLore($this['tag']['display']['Name']);
            }

            /**
             * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L285
             */
            if (isset($this['display_name']) && $this['display_name'] === 'Water Bottle') {
                $this['Damage'] = 17;
            }
        }

        /**
         * @param $rawLore
         */
        private function cleanLore($rawLore): string {
            $return = '';

            foreach (preg_split('/§/u', (string) $rawLore, -1, PREG_SPLIT_NO_EMPTY) as $part) {
                $return .= substr($part, 1);
            }

            return $return;
        }

        private function parseStats(): void {
            if (Arr::has($this, 'tag.display.Lore')) {
                $this['stats'] = new Collection();

                foreach ($this['tag']['display']['Lore'] as $line) {
                    $split = explode(':', $this->cleanLore($line));

                    if (count($split) < 2) {
                        continue;
                    }

                    $statType  = $split[0];
                    $statValue = (float)trim(str_replace(',', '', $split[1]));

                    if (Str::is([
                        'damage',
                        'health',
                        'defense',
                        'strength',
                        'speed',
                        'crit_chance',
                        'crit_damage',
                        'intelligence',
                        'sea_creature_chance',
                        'magic_find',
                        'pet_luck',
                    ], Str::snake($statType))) {
                        $this['stats'][Str::snake($statType)] = $statValue;
                    }
                }

                /**
                 * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L447
                 */
                if (Arr::has($this, 'tag.ExtraAttributes.id') && $this['tag']['ExtraAttributes']['id'] === 'SPEED_TALISMAN') {
                    foreach ($this['tag']['display']['Lore'] as $line) {
                        $line = $this->cleanLore($line);

                        if (Str::startsWith($line, 'Gives')) {
                            $split = explode('Gives +', $line);

                            if (count($split) < 2) {
                                continue;
                            }

                            $speed = (int)$split[1];

                            if ($speed !== 0) {
                                $this['stats']['speed'] = $speed;
                            }
                        }
                    }
                }
            }
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L464
         */
        private function parseItemType(): void {
            if (Arr::has($this, 'tag.display.Lore') && count($lore = $this['tag']['display']['Lore']) > 0) {
                $rarityAndType = $this->cleanLore($lore[count($lore) - 1]);

                $this['rarity'] = strtolower(Str::before($rarityAndType, ' '));

                if (Str::contains($rarityAndType, ' ')) {
                    $this['type'] = strtolower(Str::after($rarityAndType, ' '));
                }
            }

            if (Str::is(['SNOW_CANNON', 'SNOW_BLASTER'], $this->getTagId())) {
                $this['type'] = 'bow';
            }

            if (Arr::has($this, 'tag.ExtraAttributes.enchantments') && $this->getTagId() !== 'ENCHANTED_BOOK' && !self::$dataParser->get('item_types')->contains($this['type'])) {
                /** @var Collection $enchantments */
                $enchantments = $this['tag']['ExtraAttributes']['enchantments'];

                if (Arr::hasAny($enchantments, [
                    'sharpness',
                    'crticial',
                    'ender_slayer',
                    'execute',
                    'first_strike',
                    'giant_killer',
                    'lethality',
                    'life_steal',
                    'looting',
                    'luck',
                    'scavenger',
                    'vampirism',
                    'bane_of_arthropods',
                    'smite'
                ])) {
                    $this['type'] = 'sword';
                }

                if (Arr::hasAny($enchantments, [
                    'power',
                    'aiming',
                    'infinite_quiver',
                    'power',
                    'snipe',
                    'punch',
                    'flame',
                    'piercing'
                ])) {
                    $this['type'] = 'bow';
                }

                if (Arr::hasAny($enchantments, [
                    'angler',
                    'blessing',
                    'caster',
                    'frail',
                    'luck_of_the_sea',
                    'lure',
                    'magnet'
                ])) {
                    $this['type'] = 'fishing rod';
                }
            }
        }

        public function getTagId(): ?string {
            if (Arr::has($this->data, 'tag.ExtraAttributes.id')) {
                return $this['tag']['ExtraAttributes']['id'];
            }
            return null;
        }

        public static function setDataParser(SkyBlockStatsDataParser $dataParser): void {
            self::$dataParser = $dataParser;
        }

        public static function setUuidFactory(UuidFactory $uuidFactory): void {
            self::$uuidFactory = $uuidFactory;
        }

        public function __clone() {
            $this->data = $this->cloneObject($this->data);
        }

        /**
         * @param $object
         *
         * @return mixed
         */
        private function cloneObject($object) {
            if (!is_object($object)) {
                return $object;
            }

            $clonedObject = clone $object;

            if (method_exists($clonedObject, 'transform')) {
                $clonedObject->transform(fn($item) => $this->cloneObject($item));
            }
            return $clonedObject;
        }

        /**
         * Get the instance as an array.
         */
        public function toArray(): array {
            return $this->data->toArray();
        }

        /**
         * Whether a offset exists
         *
         * @link https://php.net/manual/en/arrayaccess.offsetexists.php
         *
         * @param mixed $offset <p>
         *                      An offset to check for.
         *                      </p>
         *
         * @return bool
         * </p>
         * <p>
         * The return value will be casted to boolean if non-boolean was returned.
         */
        public function offsetExists($offset): bool {
            return $this->data->offsetExists($offset);
        }

        /**
         * Offset to retrieve
         *
         * @link https://php.net/manual/en/arrayaccess.offsetget.php
         *
         * @param mixed $offset <p>
         *                      The offset to retrieve.
         *                      </p>
         *
         * @return mixed Can return all value types.
         */
        public function offsetGet($offset) {
            if ($offset === 'tag_id') {
                return $this->getTagId();
            }

            return $this->data->offsetExists($offset) ? $this->data->offsetGet($offset) : null;
        }

        /**
         * Offset to set
         *
         * @link https://php.net/manual/en/arrayaccess.offsetset.php
         *
         * @param mixed $offset <p>
         *                      The offset to assign the value to.
         *                      </p>
         * @param mixed $value  <p>
         *                      The value to set.
         *                      </p>
         */
        public function offsetSet($offset, $value): void {
            $this->data->offsetSet($offset, $value);
        }

        /**
         * Offset to unset
         *
         * @link https://php.net/manual/en/arrayaccess.offsetunset.php
         *
         * @param mixed $offset <p>
         *                      The offset to unset.
         *                      </p>
         */
        public function offsetUnset($offset): void {
            $this->data->offsetUnset($offset);
        }

        /**
         * Convert the object to its JSON representation.
         *
         * @param int $options
         */
        public function toJson($options = 0): string {
            return $this->data->toJson($options);
        }

        /**
         * Specify data which should be serialized to JSON
         *
         * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
         * @return mixed data which can be serialized by <b>json_encode</b>,
         * which is a value of any type other than a resource.
         * @since 5.4
         */
        public function jsonSerialize() {
            return $this->data->jsonSerialize();
        }

        /**
         * @param $name
         * @param $arguments
         *
         * @return mixed
         */
        public function __call($name, $arguments) {
            return $this->data->{$name}(...$arguments);
        }
    }
