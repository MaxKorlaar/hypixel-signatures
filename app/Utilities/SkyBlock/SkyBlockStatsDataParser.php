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

    /**
     * Created by Max in 2020
     */

    namespace App\Utilities\SkyBlock;

    use Cache;
    use File;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    use Log;
    use Plancke\HypixelPHP\resources\games\SkyBlockResources;
    use Plancke\HypixelPHP\responses\player\Player;
    use Plancke\HypixelPHP\responses\Resource;
    use pocketmine\nbt\BigEndianNBTStream;
    use pocketmine\nbt\tag\CompoundTag;
    use pocketmine\nbt\tag\ListTag;
    use pocketmine\nbt\tag\NamedTag;
    use UnexpectedValueException;

    /**
     * Class SkyBlockStatsDataParser
     * Parse data from the https://github.com/LeaPhant/skyblock-stats/ project written in JavaScript.
     *
     * @package App\Utilities\SkyBlock
     */
    class SkyBlockStatsDataParser {
        public const MAX_SOULS = 194;
        public const PET_TIERS = ['common', 'uncommon', 'rare', 'epic', 'legendary'];
        public const RARITY_ORDER = ['special', 'legendary', 'epic', 'rare', 'uncommon', 'common'];

        /**
         * @var Collection
         */
        private $constants;

        public function __construct() {
            $this->constants = Cache::rememberForever('skyblock.constants', static function () {
                return new Collection(json_decode(File::get(resource_path('data/skyblock/constants.json')), true));
            });
        }

        /**
         * @return Collection
         */
        public function getConstants(): Collection {
            return $this->constants;
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/master/src/lib.js#L1018
         *
         *
         * @param Collection $profile
         * @param Player     $player
         *
         * @return Collection
         * @todo implement caching
         */
        public function getSkyBlockProfile(Collection $profile, Player $player): Collection {
            $return = new Collection([
                'stats'                          => $this->getBaseStats(),
                'profile'                        => $profile,
                'fairy_bonus'                    => new Collection(),
                'average_level'                  => 0,
                'average_level_without_progress' => 0,
                'total_skill_xp'                 => 0,
                'levels'                         => new Collection(),
                'skill_bonus'                    => new Collection(),
                'slayer_coins_spent'             => new Collection([
                    'total' => 0
                ]),
                'slayer_xp'                      => 0,
                'slayer_bonus'                   => new Collection()
            ]);

            /**
             * Fairy bonus
             *
             * @link https://github.com/LeaPhant/skyblock-stats/blob/c2269c76ec028c31bd37d5bf7ffd90292f5547c2/src/lib.js#L1026
             */

            if (($profile['fairy_exchanges'] ?? 0) > 0) {
                $fairySoulsBonusStats = new Collection($this->getBonusStats()->get('fairy_souls'));

                $fairyBonus            = $this->getBonusStat($profile['fairy_exchanges'] * 5, 'fairy_souls', $fairySoulsBonusStats->keys()->max(), 5);
                $return['fairy_bonus'] = $fairyBonus;

                foreach ($fairyBonus as $statName => $value) {
                    $return['stats'][$statName] += $value;
                }
            }

            $return['fairy_souls'] = [
                'collected' => $profile['fairy_souls_collected'] ?? 0,
                'total'     => self::MAX_SOULS,
                'progress'  => min($profile['fairy_souls_collected'] ?? 0 / self::MAX_SOULS, 1)
            ];

            $totalSkillXp = 0;
            $averageLevel = 0;

            /**
             * Skills
             * sky.lea.moe calculates your skills based on your achievements if you haven't enabled them in the API.
             * I don't do this – these signatures are meant for personal use so it'd be pointless to want to use these and not enable these features for my site.
             *
             * @link https://github.com/LeaPhant/skyblock-stats/blob/c2269c76ec028c31bd37d5bf7ffd90292f5547c2/src/lib.js#L1083
             */

            $skyBlockSkills = $this->getSkyBlockSkills($player->getHypixelPHP()->getResourceManager()->getGameResources()->getSkyBlock());

            $skillCollections = new Collection($skyBlockSkills->getArray('collections'));

            $averageLevelWithoutProgress = 0;

            $skillLevels = $skillCollections->keys()->mapWithKeys(function ($value) use ($profile) {
                return [strtolower($value) => $this->getLevelByXp($profile['experience_skill_' . strtolower($value)] ?? 0)];
            });

            foreach ($skillLevels as $skill => $value) {
                if ($skill === 'runecrafting' || $skill === 'carpentry') {
                    continue;
                }

                $averageLevel                += $value['level'] + $value['progress'];
                $averageLevelWithoutProgress += $value['level'];
                $totalSkillXp                += $value['xp'];
            }

            $return['average_level']                  = $averageLevel / (count($skillLevels) - 2); // - 2 because runecrafting and carpentry are not included
            $return['average_level_without_progress'] = $averageLevelWithoutProgress / (count($skillLevels) - 2);
            $return['total_skill_xp']                 = $totalSkillXp;
            $return['levels']                         = $skillLevels;

            /**
             * Skill bonus
             *
             * @link https://github.com/LeaPhant/skyblock-stats/blob/c2269c76ec028c31bd37d5bf7ffd90292f5547c2/src/lib.js#L1116
             */

            foreach ($skillLevels as $skill => $value) {
                if ($value['level'] === 0) {
                    continue;
                }

                $skillBonus = $this->getBonusStat($value['level'], $skill . '_skill', 50, 1);

                $return['skill_bonus'][$skill] = $skillBonus;

                foreach ($skillBonus as $stat => $bonus) {
                    $return['stats'][$stat] += $bonus;
                }
            }

            /**
             * Slayer bonuses and coins spent
             *
             * @link https://github.com/LeaPhant/skyblock-stats/blob/c2269c76ec028c31bd37d5bf7ffd90292f5547c2/src/lib.js#L1130
             */

            if (isset($profile['slayer_bosses'])) {
                $slayers = new Collection();

                foreach ($profile['slayer_bosses'] as $slayerName => $slayer) {
                    $returnSlayer = $slayers[$slayerName] = new Collection();

                    if (!isset($slayer['claimed_levels'])) {
                        continue;
                    }

                    $returnSlayer['level']                     = $this->getSlayerLevel($slayer, $slayerName);
                    $returnSlayer['kills']                     = new Collection();
                    $return['slayer_coins_spent'][$slayerName] = 0;

                    foreach ($slayer as $key => $value) {
                        $returnSlayer[$key] = $value;

                        if (Str::startsWith($key, 'boss_kills_tier_')) {
                            $tier = ((int)Str::afterLast($key, 'boss_kills_tier_')) + 1;

                            $returnSlayer['kills'][$tier]              = $value;
                            $return['slayer_coins_spent'][$slayerName] = $return['slayer_coins_spent'][$slayerName] + $value * $this->get('slayer_cost')->get($tier);
                        }
                    }

                    $slayers[$slayerName] = $returnSlayer;
                }

                foreach ($return['slayer_coins_spent'] as $slayerName => $coinsSpent) {
                    $return['slayer_coins_spent']['total'] += $coinsSpent;
                }

                foreach ($slayers as $slayerName => $slayer) {
                    if (!Arr::has($slayer, 'level.currentLevel') || !isset($slayer['xp'])) {
                        continue;
                    }

                    $slayerBonus = $this->getBonusStat($slayer['level']['currentLevel'], $slayerName . '_slayer', 9, 1);

                    $return['slayer_bonus'][$slayerName] = $slayerBonus;

                    $return['slayer_xp'] += $slayer['xp'];

                    foreach ($slayerBonus as $stat => $value) {
                        $return['stats'][$stat] += $value;
                    }
                }

                $return['slayers'] = $slayers;
            }

            /**
             * Pet score and bonus
             *
             * @link https://github.com/LeaPhant/skyblock-stats/blob/c2269c76ec028c31bd37d5bf7ffd90292f5547c2/src/lib.js#L1190
             */

            $return['pets']      = $this->getPets($profile);
            $return['pet_score'] = $this->getPetScore($return['pets']);

            $requiredPetScore = $this->get('pet_rewards')->keys()->sort();

            foreach ($requiredPetScore as $index => $score) {
                if ($score > $return['pet_score']) {
                    continue;
                }

                $return['pet_score_bonus'] = $this->get('pet_rewards')->get($score);
            }

            foreach ($return['pets'] as $pet) {
                if (!$pet['active']) {
                    continue;
                }

                foreach ($pet['stats'] as $stat => $value) {
                    $return['pet_bonus'][$stat] = ($return['pet_bonus'][$stat] ?? 0) + $value;
                }
            }

            /**
             * Harp bonuses
             *
             * @link https://github.com/LeaPhant/skyblock-stats/blob/c2269c76ec028c31bd37d5bf7ffd90292f5547c2/src/lib.js#L1215
             */

            $items = $this->getItems($profile);

            dd($items, $profile);

            dd($return, $slayers, $profile, $requiredPetScore);

            dd('no_skills', $profile->has('experience_skill_taming'));

            return $return;
        }

        /**
         * @return Collection
         */
        public function getBaseStats(): Collection {
            return $this->get('base_stats');
        }

        /**
         * @param $item
         *
         * @return Collection
         */
        public function get($item): Collection {
            return new Collection($this->constants->get($item));
        }

        /**
         * @return Collection
         */
        public function getBonusStats(): Collection {
            return $this->get('bonus_stats');
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/master/src/lib.js#L175
         *
         * @param $level
         * @param $skill
         * @param $max
         * @param $incremention
         *
         * @return Collection
         */
        protected function getBonusStat($level, $skill, $max, $incremention): Collection {
            $skillBonusStats = new Collection($this->getBonusStats()->get($skill));
            $steps           = $skillBonusStats->keys()->sort();

            $bonus = $this->getStatTemplate();

            for ($skillLevel = $steps[0]; $skillLevel <= $max; $skillLevel += $incremention) {
                if ($level < $skillLevel) {
                    break;
                }

                $skillStep  = $steps->reverse()->firstWhere(null, '<=', $skillLevel);
                $skillBonus = $skillBonusStats[$skillStep];

                foreach ($skillBonus as $skillName => $value) {
                    $bonus[$skillName] += $value;
                }
            }

            return $bonus;
        }

        /**
         * @return Collection
         */
        public function getStatTemplate(): Collection {
            return $this->get('stat_template');
        }

        /**
         * @param SkyBlockResources $resources
         *
         * @noinspection PhpIncompatibleReturnTypeInspection
         * @noinspection PhpDocMissingReturnTagInspection
         */
        protected function getSkyBlockSkills(SkyBlockResources $resources): Resource {
            $skills = $resources->getSkills();

            if (($skills instanceof Resource) && !empty($skills->getData())) {
                return $skills;
            }

            Log::debug('Unexpected API response for SkyBlock skills', ['response' => $skills]);

            throw new UnexpectedValueException('Unexpected API response for SkyBlock skills');
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/master/src/lib.js#L56
         *
         * @param      $xp
         * @param bool $runecrafting
         *
         * @return array
         */
        public function getLevelByXp($xp, $runecrafting = false): array {
            $xpTable = $runecrafting ? $this->get('runecrafting_xp') : $this->get('leveling_xp');

            $totalXp        = 0;
            $level          = 0;
            $xpForNextLevel = INF;

            $maxLevel = $xpTable->keys()->sort()->pop();

            for ($xpLevel = 1; $xpLevel <= $maxLevel; $xpLevel++) {
                $totalXp += $xpTable[$xpLevel];

                if ($totalXp > $xp) {
                    $totalXp -= $xpTable[$xpLevel];
                    break;
                }

                $level = $xpLevel;
            }

            $currentXp = floor($xp - $totalXp);

            if ($level < $maxLevel) {
                $xpForNextLevel = ceil($xpTable[$level + 1]);
            }

            $progress = max(0, min($currentXp / $xpForNextLevel, 1));

            return compact('xp', 'level', 'maxLevel', 'currentXp', 'xpForNextLevel', 'progress');
        }

        /**
         * @param array  $slayer
         * @param string $slayerName
         *
         * @return array
         */
        protected function getSlayerLevel(array $slayer, string $slayerName): array {
            $xp            = $slayer['xp'] ?? 0;
            $claimedLevels = $slayer['claimed_levels'] ?? [];

            $currentLevel   = 0;
            $xpForNextLevel = 0;

            $slayerXpTable = $this->get('slayer_xp');
            $slayerXp      = new Collection($slayerXpTable->get($slayerName));

            $maxLevel = $slayerXp->keys()->max();

            foreach ($claimedLevels as $levelName => $claimed) {
                $level = (int)Str::afterLast($levelName, '_');

                if ($level > $currentLevel) {
                    $currentLevel = $level;
                }
            }

            if ($currentLevel < $maxLevel) {
                $nextLevel = $slayerXp[$currentLevel + 1];

                $progress       = $xp / $nextLevel;
                $xpForNextLevel = $nextLevel;
            } else {
                $progress = 1;
            }

            return compact('currentLevel', 'xp', 'maxLevel', 'progress', 'xpForNextLevel');
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/c2269c76ec028c31bd37d5bf7ffd90292f5547c2/src/lib.js#L1428
         *
         * @param Collection $profile
         *
         * @return Collection
         */
        protected function getPets(Collection $profile): Collection {
            $return = new Collection();

            if (!isset($profile['pets'])) {
                return $return;
            }
            foreach ($profile['pets'] as $pet) {
                if (!isset($pet['tier'])) {
                    continue;
                }

                $pet['rarity'] = strtolower($pet['tier']);

                if ($pet['heldItem'] === 'PET_ITEM_TIER_BOOST') {
                    $pet['rarity'] = self::PET_TIERS[min(count(self::PET_TIERS) - 1, array_search($pet['rarity'], self::PET_TIERS) + 1)];
                }

                $pet['level'] = $this->getPetLevel($pet);
                $pet['stats'] = [];

                $petData = $this->get('pet_data')->get($pet['type'], [
                    'type' => null,
                    'head' => '/head/bc8ea1f51f253ff5142ca11ae45193a4ad8c3ab5e9c6eec8ba7a4fcb7bac40'
                ]);

                $pet['texture_name'] = Str::afterLast($petData['head'], '/head/');

                if ($pet['heldItem'] !== null) {
                    $heldItem = $pet['heldItem'];

                    $petItemData = $this->get('pet_items')->get($heldItem);
                    if ($petItemData !== null) {
                        foreach ($petItemData['stats'] ?? [] as $stat => $value) {
                            $pet['stats'][$stat] = ($pet['stats'][$stat] ?? 0) + $value;
                        }
                    }
                }

                $pet['display_name'] = Str::title(str_replace('_', ' ', $pet['type']));

                $return[] = $pet;
            }

            return $return->sort(static function ($a, $b) use ($return) {
                if ($a['active'] === $b['active']) {
                    if ($a['rarity'] === $b['rarity']) {
                        if ($a['type'] === $b['type']) {
                            return $b['level']['level'] - $a['level']['level'];
                        }

                        $maxPetA = $return->filter(function ($pet) use ($a) {
                            return $pet['type'] === $a['type'] && $pet['rarity'] === $a['rarity'];
                        })->sort(static function ($x, $y) {
                            return $y['level']['level'] - $x['level']['level'];
                        });

                        $maxPetA = $maxPetA->isNotEmpty() ? $maxPetA->first()['level']['level'] : null;

                        $maxPetB = $return->filter(function ($pet) use ($b) {
                            return $pet['type'] === $b['type'] && $pet['rarity'] === $b['rarity'];
                        })->sort(static function ($x, $y) {
                            return $y['level']['level'] - $x['level']['level'];
                        });

                        $maxPetB = $maxPetB->isNotEmpty() ? $maxPetB->first()['level']['level'] : null;

                        if ($maxPetA && $maxPetB && $maxPetA === $maxPetB) {
                            return $b['level']['currentXp'] - $a['level']['currentXp'];
                        }

                        return $maxPetB - $maxPetA;
                    }

                    return array_search($a['rarity'], self::RARITY_ORDER, true) - array_search($b['rarity'], self::RARITY_ORDER, true);
                }

                return $a['active'] ? -1 : 1;
            });
        }

        /**
         * @param $pet
         *
         * @return array
         */
        protected function getPetLevel($pet): array {
            $rarityOffset = $this->get('pet_rarity_offset')->get($pet['rarity']);
            $levels       = $this->get('pet_levels')->slice($rarityOffset, $rarityOffset + 99);

            $xpForMaxLevel = $levels->sum();
            $totalXp       = 0;
            $level         = 1;

            if ($pet['exp'] >= $xpForMaxLevel) {
                $level   = 100;
                $totalXp = $pet['exp'];
            } else {
                for ($petLevel = 0; $petLevel < 100; $petLevel++) {
                    $totalXp += $levels->values()[$petLevel];

                    if ($totalXp > $pet['exp']) {
                        $totalXp -= $levels->values()[$petLevel];
                        break;
                    }

                    $level++;
                }
            }

            $currentXp = floor($pet['exp'] - $totalXp);

            if ($level < 100) {
                $xpForNextLevel = ceil($levels->values()[$level - 1]);
                $progress       = max(0, min($currentXp / $xpForNextLevel, 1));
            } else {
                $level          = 100;
                $currentXp      = $pet['exp'] - $levels->values()->last();
                $xpForNextLevel = 0;
                $progress       = 1;
            }

            return compact('level', 'currentXp', 'xpForNextLevel', 'progress', 'xpForMaxLevel');
        }

        /**
         * @param Collection $pets
         *
         * @return int
         */
        protected function getPetScore(Collection $pets): int {
            $highestRarity = new Collection();
            $petValues     = $this->get('pet_value');

            foreach ($pets as $pet) {
                if (!isset($highestRarity[$pet['type']]) || $petValues->get($pet['rarity']) > $highestRarity[$pet['type']]) {
                    $highestRarity[$pet['type']] = $petValues->get($pet['rarity']);
                }
            }

            return $highestRarity->sum();
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L657
         *
         * @param Collection $profile
         */
        protected function getItems(Collection $profile) {
            $armor       = isset($profile['inv_armor']) ? $this->getItemsFromData($profile['inv_armor']['data']) : [];
            $inventory   = isset($profile['inv_contents']) ? $this->getItemsFromData($profile['inv_contents']['data']) : [];
            $enderchest  = isset($profile['ender_chest_contents']) ? $this->getItemsFromData($profile['ender_chest_contents']['data']) : [];
            $talismanBag = isset($profile['talisman_bag']) ? $this->getItemsFromData($profile['talisman_bag']['data']) : [];
            $fishingBag  = isset ($profile['fishing_bag']) ? $this->getItemsFromData($profile['fishing_bag']['data']) : [];
            $quiver      = isset($profile['quiver']) ? $this->getItemsFromData($profile['quiver']['data']) : [];
            $potionBag   = isset($profile['potion_bag']) ? $this->getItemsFromData($profile['potion_bag']['data']) : [];
            $candyBag    = isset($profile['candy_inventory_contents']) ? $this->getItemsFromData($profile['candy_inventory_contents']['data']) : [];

            $return['armor']        = $armor->filter(static function ($item) {
                /** @var Collection $item */
                return $item->isNotEmpty();
            });
            $return['inventory']    = $inventory;
            $return['enderchest']   = $enderchest;
            $return['talisman_bag'] = $talismanBag;
            $return['fishing_bag']  = $fishingBag;
            $return['quiver']       = $quiver;
            $return['potion_bag']   = $potionBag;

            $allItems = $armor->concat($inventory)->concat($enderchest)->concat($talismanBag)
                ->concat($fishingBag)->concat($quiver)->concat($potionBag)
                ->filter(static function ($item) {
                    /** @var Collection $item */
                    return $item->isNotEmpty();
                });

            $enderchest = $enderchest->map(static function ($item) {
                $item['is_inactive'] = true;
                return $item;
            });
            //https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L692

            dd($return, $allItems);
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L228
         *
         * @param $dataBase64
         *
         * @return Collection
         */
        private function getItemsFromData($dataBase64): Collection {
            $data = gzdecode(base64_decode($dataBase64));

            $nbtStream = new BigEndianNBTStream();
            $nbtData   = $nbtStream->read($data);

            /** @var ListTag $itemsTag */
            $itemsTag = $nbtData->getValue()['i'];
            $nbtItems = $itemsTag->getValue();

            $items = new Collection();

            foreach ($nbtItems as $index => $nbtItem) {
                $item = $this->simplify($nbtItem);

                if (Arr::has($item, 'tag.display.Name') &&
                    Str::endsWith($item['tag']['display']['Name'], ['Backpack', 'New Year Cake Bag'])) {
                    // Item is a backpack

                    $extraAttributes = $item['tag']['ExtraAttributes'];
                    $backpackData    = null;

                    foreach ($extraAttributes as $name => $attribute) {
                        if (Str::endsWith($name, ['backpack_data', 'new_year_cake_bag_data'])) {
                            $backpackData = $attribute;
                        }
                    }

                    if ($backpackData === null) {
                        continue;
                    }

                    $item['contains_items'] = $this->getBackpackContents($backpackData);
                }

                $items[] = $item;
            }

            foreach ($items as $item) {

                if (Arr::has($item, 'tag.display.Name')) {
                    $item['display_name'] = $this->cleanLore($item['tag']['display']['Name']);
                }

                /**
                 * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L285
                 */
                if (isset($item['display_name']) && $item['display_name'] === 'Water Bottle') {
                    $item['Damage'] = 17;
                }

                $rarity   = null;
                $itemType = null;

                if (Arr::has($item, 'tag.display.Lore')) {
                    $item['stats'] = new Collection();

                    foreach ($item['tag']['display']['Lore'] as $line) {
                        $split = explode(':', $this->cleanLore($line));

                        if (count($split) < 2) {
                            continue;
                        }

                        $statType  = $split[0];
                        $statValue = (float)trim(str_replace(',', '', $split[1]));

                        $item['stats'][Str::snake($statType)] = $statValue;
                    }

                    /**
                     * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L447
                     */
                    if (Arr::has($item, 'tag.ExtraAttributes.id') && $item['tag']['ExtraAttributes']['id'] === 'SPEED_TALISMAN') {
                        foreach ($item['tag']['display']['Lore'] as $line) {
                            $line = $this->cleanLore($line);

                            if (Str::startsWith($line, 'Gives')) {
                                $split = explode('Gives +', $line);

                                if (count($split) < 2) {
                                    continue;
                                }

                                $speed = (int)$split[1];

                                if ($speed !== 0) {
                                    $item['stats']['speed'] = $speed;
                                }
                            }
                        }
                    }
                }
            }

            return $items;
        }

        /**
         * @param NamedTag $item
         *
         * @return mixed|Collection
         */
        private function simplify(NamedTag $item) {
            if ($item instanceof CompoundTag || $item instanceof ListTag) {
                return (new Collection($item->getValue()))->mapWithKeys(function ($mapItem, $key) {
                    /** @var NamedTag $mapItem */
                    return [$key => $this->simplify($mapItem)];
                });
            }

            return $item->getValue();
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L204
         *
         * @param string $backpackData
         *
         * @return array
         */
        private function getBackpackContents($backpackData): array {
            $data = gzdecode($backpackData);

            $nbtStream = new BigEndianNBTStream();
            $nbtData   = $nbtStream->read($data);

            /** @var ListTag $itemsTag */
            $itemsTag = $nbtData->getValue()['i'];
            $items    = $itemsTag->getValue();

            $return = [];

            /** @var CompoundTag $itemNbt */
            foreach ($items as $index => $itemNbt) {
                $item                = $this->simplify($itemNbt);
                $item['is_inactive'] = true;
                $item['item_index']  = $index;
                $return[]            = $item;
            }

            return $return;
        }

        /**
         * @param $rawLore
         *
         * @return string
         */
        private function cleanLore($rawLore): string {
            $return = '';

            foreach (preg_split('/§/u', $rawLore, -1, PREG_SPLIT_NO_EMPTY) as $part) {
                $return .= substr($part, 1);
            }

            return $return;
        }
    }
