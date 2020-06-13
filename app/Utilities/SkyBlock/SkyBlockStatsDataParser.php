<?php
    /*
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

    namespace App\Utilities\SkyBlock;

    use App\Exceptions\HypixelFetchException;
    use App\Exceptions\SkyBlockEmptyProfileException;
    use App\Models\SkyBlockItem;
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
    use pocketmine\nbt\tag\ListTag;
    use Ramsey\Uuid\UuidFactory;
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
                return new Collection(json_decode(File::get(resource_path('data/skyblock/constants.json')), true, 512, JSON_THROW_ON_ERROR));
            });
        }

        /**
         * @param Player $player
         * @param string $id
         *
         * @return Collection
         * @throws HypixelFetchException
         * @throws SkyBlockEmptyProfileException
         * @noinspection PhpDocRedundantThrowsInspection
         */
        public static function getSkyBlockStats(Player $player, string $id): Collection {
            return Cache::remember('skyblock.profile.' . $player->getUUID() . '.' . $id . '.stats', config('cache.times.skyblock_profile'), static function () use ($id, $player) {
                $dataparser = new self();
                return $dataparser->getSkyBlockProfile($player, $id)->only(['stats', 'stats_with_sword', 'weapon_stats']);
            });
        }

        /**
         * @param Player $player
         *
         * @param string $id
         *
         * @return Collection
         * @throws HypixelFetchException
         * @throws SkyBlockEmptyProfileException
         * @noinspection PhpDocRedundantThrowsInspection
         */
        public function getSkyBlockProfile(Player $player, string $id): Collection {
            return Cache::remember('skyblock.profile.' . $player->getUUID() . '.' . $id, config('cache.times.skyblock_profile'), function () use ($id, $player) {
                $skyBlockProfile = $player->getHypixelPHP()->getSkyBlockProfile($id);

                if ($skyBlockProfile === null) {
                    throw new HypixelFetchException('SkyBlock profile for user ' . $player->getUUID() . ' is null');
                }

                $members = $skyBlockProfile->getMembers();

                if (empty($skyBlockProfile->getData())) {
                    throw new SkyBlockEmptyProfileException('SkyBlock profile ' . $id . ' has no data');
                }

                if (!isset($members[$player->getUUID()])) {
                    throw new SkyBlockEmptyProfileException('SkyBlock profile ' . $id . ' does not contain profile data for ' . $player->getUUID());
                }

                $playerProfile = $members[$player->getUUID()];

                return $this->getSkyBlockData(new Collection($playerProfile), $player);
            });
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/dd1f50231218a52eb0e66637a7827ef45e1963da/src/lib.js
         *
         *
         * @param Collection $profile
         * @param Player     $player
         *
         * @return Collection
         */
        protected function getSkyBlockData(Collection $profile, Player $player): Collection {
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
                'slayer_bonus'                   => new Collection(),
                'pet_bonus'                      => new Collection(),
                'pet_score_bonus'                => new Collection(),
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

            if ($items['talismans']->filter(static function (SkyBlockItem $item) {
                    return $item->getTagId() === 'MELODY_HAIR';
                })->count() === 1) {
                $return['stats']['intelligence'] += 26;
            }

            foreach ($return['pet_score_bonus'] as $stat => $value) {
                $return['stats'][$stat] += $value;
            }

            $return['base_stats'] = clone $return['stats'];

            /**
             * Full Lapis Armor set adds 60 HP
             *
             * @link https://github.com/LeaPhant/skyblock-stats/blob/c2269c76ec028c31bd37d5bf7ffd90292f5547c2/src/lib.js#L1228
             */
            if ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'LAPIS_ARMOR_');
                })->count() === 4) {
                $items['armor'][0]['stats']['health'] = ($items['armor'][0]['stats']['health'] ?? 0) + 60;
            } elseif (Arr::has($profile, 'collection.EMERALD') && is_numeric($profile['collection']['EMERALD']) && $items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'EMERALD_ARMOR_');
                })->count() === 4) {
                // Emerald Armor set adds 1 HP and 1 Defense per 3000 emeralds in collection
                $emeraldBonus = min(350, floor($profile['collection']['EMERALD'] / 3000));

                $items['armor'][0]['stats']['health']  += $emeraldBonus;
                $items['armor'][0]['stats']['defense'] += $emeraldBonus;
            } elseif ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'FAIRY_');
                })->count() === 4) {
                $items['armor'][0]['stats']['speed'] += 10;
            } elseif ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'SPEEDSTER_');
                })->count() === 4) {
                $items['armor'][0]['stats']['speed'] += 20;
            } elseif ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'YOUNG_DRAGON_');
                })->count() === 4) {
                $items['armor'][0]['stats']['speed'] += 70;
            }

            /**
             * Apply armor stats
             *
             * @link https://github.com/LeaPhant/skyblock-stats/blob/c2269c76ec028c31bd37d5bf7ffd90292f5547c2/src/lib.js#L1253
             *
             * @var SkyBlockItem $armorPiece
             */
            foreach ($items['armor'] as $armorPiece) {
                if ($armorPiece['is_inactive']) {
                    $armorPiece['stats'] = [];
                }

                foreach ($armorPiece['stats'] as $stat => $value) {
                    $return['stats'][$stat] += $value;
                }
            }

            foreach ($items['talismans']->where('is_inactive', false) as $talisman) {
                foreach ($talisman['stats'] as $stat => $value) {
                    $return['stats'][$stat] += $value;
                }
            }

            /**
             * Mastiff armor set bonus
             * Presumably placed after the talismans because it takes into account the player's crit damage
             */
            if ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'MASTIFF_');
                })->count() === 4) {
                $return['stats']['health']            += 50 * $return['stats']['crit_damage'];
                $items['armor'][0]['stats']['health'] += 50 * $return['stats']['crit_damage'];
            }

            if ($items['talismans']->where('is_inactive', false)->filter(static function (SkyBlockItem $talisman) {
                    return Str::is(['DAY_CRYSTAL', 'NIGHT_CRYSTAL'], $talisman->getTagId());
                })->count() === 2) {
                $return['stats']['defense']  += 5;
                $return['stats']['strength'] += 5;
            }

            /**
             * Obsidian chestplate bonus
             */
            if ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return $armorPiece->getTagId() === 'OBSIDIAN_CHESTPLATE';
                })->count() === 1) {
                $obsidian = 0;

                foreach ($items['inventory'] as $item) {
                    if ($item['id'] === 49) {
                        $obsidian += $item['Count'];
                    }
                }

                $return['stats']['speed'] += floor($obsidian / 20);
            }

            if ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'CHEAP_TUXEDO_');
                })->count() === 3) {
                $return['stats']['health'] = 75;
            } elseif ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'FANCY_TUXEDO_');
                })->count() === 3) {
                $return['stats']['health'] = 150;
            } elseif ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'ELEGANT_TUXEDO_');
                })->count() === 3) {
                $return['stats']['health'] = 250;
            }

            $return['weapon_stats'] = new Collection();

            foreach ($items['weapons']->concat($items['rods']) as $item) {
                $weaponStats = clone $return['stats'];

                if (Arr::has($item, 'tag.ExtraAttributes.enchantments.angler')) {
                    $item['stats']['sea_creature_chance'] = ($item['stats']['sea_creature_chance'] ?? 0) + $item['tag']['ExtraAttributes']['enchantments']['angler'];
                }

                foreach ($item['stats'] as $stat => $value) {
                    $weaponStats[$stat] += $value;
                }

                if (isset($item['stats']['crit_damage']) && $item['stats']['crit_damage'] > 0 && $items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                        return Str::startsWith($armorPiece->getTagId(), 'MASTIFF_');
                    })->count() === 4) {
                    $weaponStats['health'] += 50 * $item['stats']['crit_damage'];
                }

                if ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                        return Str::startsWith($armorPiece->getTagId(), 'SUPERIOR_DRAGON_');
                    })->count() === 4) {
                    foreach ($weaponStats as $stat => $val) {
                        $weaponStats[$stat] = floor($val * 1.05);
                    }
                } elseif ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                        return Str::startsWith($armorPiece->getTagId(), 'CHEAP_TUXEDO_');
                    })->count() === 3) {
                    $weaponStats['health'] = 75;
                } elseif ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                        return Str::startsWith($armorPiece->getTagId(), 'FANCY_TUXEDO_');
                    })->count() === 3) {
                    $weaponStats['health'] = 150;
                } elseif ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                        return Str::startsWith($armorPiece->getTagId(), 'ELEGANT_TUXEDO_');
                    })->count() === 3) {
                    $weaponStats['health'] = 250;
                }

                $weaponStats['effective_health'] = $this->getEffectiveHealth($weaponStats['health'], $weaponStats['defense']);

                foreach ($weaponStats as $stat => $val) {
                    $weaponStats[$stat] = max(0, round($val));
                }

                $return['weapon_stats'][$item['item_uuid']] = $weaponStats;
            }

            $superiorBonus = $this->get('stat_template');

            /**
             * Superior Dragon Armor set bonus
             *
             * @link https://github.com/LeaPhant/skyblock-stats/blob/master/src/lib.js#L1278
             */

            if ($items['armor']->filter(static function (SkyBlockItem $armorPiece) {
                    return Str::startsWith($armorPiece->getTagId(), 'SUPERIOR_DRAGON_');
                })->count() === 4) {

                foreach ($return['stats'] as $stat => $value) {
                    $superiorBonus[$stat] = floor($value * 0.05);
                }

                foreach ($superiorBonus as $stat => $value) {
                    $return['stats'][$stat] += $superiorBonus[$stat];

                    if (!isset($items['armor'][0]['stats'][$stat])) {
                        $items['armor'][0]['stats'][$stat] = 0;
                    }

                    $items['armor'][0]['stats'][$stat] += $superiorBonus[$stat];
                }

            }

            foreach ($return['stats'] as $stat => $val) {
                $return['stats'][$stat] = max(0, round($val));
            }

            $return['stats']['effective_health'] = $this->getEffectiveHealth($return['stats']['health'], $return['stats']['defense']);

            if ($items['highest_rarity_sword'] !== null) {
                $sword = $items['highest_rarity_sword'];
            } else {
                $sword = $items['weapons']->sort(static function ($a, $b) {
                    return $a['item_index'] - $b['item_index'];
                })->first();
            }

            if ($sword !== null) {
                $return['stats_with_sword'] = $return['weapon_stats'][$sword['item_uuid']];
            }

            if ($items['highest_rarity_rod'] !== null) {
                $rod = $items['highest_rarity_rod'];
            } else {
                $rod = $items['rods']->sort(static function ($a, $b) {
                    return $a['item_index'] - $b['item_index'];
                })->first();
            }

            if ($rod !== null) {
                if (isset($return['stats_with_sword'])) {
                    $return['stats_with_sword']['sea_creature_chance'];
                }

                $return['stats']['sea_creature_chance'] = $return['weapon_stats'][$rod['item_uuid']]['sea_creature_chance'];
            }

            $return['items'] = $items;

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

                if (isset($pet['heldItem']) && $pet['heldItem'] === 'PET_ITEM_TIER_BOOST') {
                    $pet['rarity'] = self::PET_TIERS[min(count(self::PET_TIERS) - 1, array_search($pet['rarity'], self::PET_TIERS) + 1)];
                }

                $pet['level'] = $this->getPetLevel($pet);
                $pet['stats'] = [];

                $petData = $this->get('pet_data')->get($pet['type'], [
                    'type' => null,
                    'head' => '/head/bc8ea1f51f253ff5142ca11ae45193a4ad8c3ab5e9c6eec8ba7a4fcb7bac40'
                ]);

                $pet['texture_name'] = Str::afterLast($petData['head'], '/head/');

                if (isset($pet['heldItem']) && $pet['heldItem'] !== null) {
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
         *
         * @return Collection
         */
        protected function getItems(Collection $profile): Collection {
            SkyBlockItem::setDataParser($this);
            SkyBlockItem::setUuidFactory(new UuidFactory());

            $armor       = isset($profile['inv_armor']) ? $this->getItemsFromData($profile['inv_armor']['data']) : new Collection();
            $inventory   = isset($profile['inv_contents']) ? $this->getItemsFromData($profile['inv_contents']['data']) : new Collection();
            $enderchest  = isset($profile['ender_chest_contents']) ? $this->getItemsFromData($profile['ender_chest_contents']['data']) : new Collection();
            $talismanBag = isset($profile['talisman_bag']) ? $this->getItemsFromData($profile['talisman_bag']['data']) : new Collection();
            $fishingBag  = isset ($profile['fishing_bag']) ? $this->getItemsFromData($profile['fishing_bag']['data']) : new Collection();
            $quiver      = isset($profile['quiver']) ? $this->getItemsFromData($profile['quiver']['data']) : new Collection();
            $potionBag   = isset($profile['potion_bag']) ? $this->getItemsFromData($profile['potion_bag']['data']) : new Collection();
            $candyBag    = isset($profile['candy_inventory_contents']) ? $this->getItemsFromData($profile['candy_inventory_contents']['data']) : new Collection();

            $return = new Collection([
                'highest_rarity_sword' => null,
                'highest_rarity_bow'   => null,
                'highest_rarity_rod'   => null
            ]);

            $return['armor']        = $armor->filter(static function ($item) {
                /** @var SkyBlockItem $item */
                return $item->hasData();
            });
            $return['inventory']    = $inventory;
            $return['enderchest']   = $enderchest;
            $return['talisman_bag'] = $talismanBag;
            $return['fishing_bag']  = $fishingBag;
            $return['quiver']       = $quiver;
            $return['potion_bag']   = $potionBag;

            /** @var Collection|SkyBlockItem[] $allItems */
            $allItems = $armor->concat($inventory)->concat($enderchest)->concat($talismanBag)
                ->concat($fishingBag)->concat($quiver)->concat($potionBag)
                ->filter(static function ($item) {
                    /** @var SkyBlockItem $item */
                    return $item->hasData();
                });

            $enderchest = $enderchest->map(static function ($item) {
                $item['is_inactive'] = true;
                return $item;
            });

            foreach ($allItems as $index => $item) {
                if (!$item->hasData()) {
                    continue;
                }

                $item['item_index'] = $index;
                if ($item->getTagId() === 'TRICK_OR_TREAT_BAG') {
                    $item['contains_items'] = $candyBag;
                }
            }

            /**
             * Talismans
             */

            $talismans = new Collection();

            /** @var SkyBlockItem $talisman */
            foreach ($armor->where('type', 'accessory') as $talisman) {
                $id = $talisman->getTagId();

                if ($id === null) {
                    continue;
                }

                $talisman['is_unique']   = true;
                $talisman['is_inactive'] = false;

                if ($talismans->filter(static function (SkyBlockItem $talisman) use ($id) {
                    return !$talisman['is_inactive'] && $talisman->getTagId() === $id;
                })->isNotEmpty()) {
                    $talisman['is_inactive'] = true;
                }

                if ($talismans->filter(static function (SkyBlockItem $talisman) use ($id) {
                    return $talisman->getTagId() === $id;
                })->isNotEmpty()) {
                    $talisman['is_unique'] = false;
                }
            }

            /**
             * Adds the talismans from the inventory to the collection
             *
             * @var SkyBlockItem $talisman
             */
            foreach ($inventory->where('type', 'accessory')->concat($talismanBag) as $talisman) {
                $id = $talisman->getTagId();

                if ($id === null) {
                    continue;
                }

                $talisman['is_unique']   = true;
                $talisman['is_inactive'] = false;

                if ($talismans->filter(static function (SkyBlockItem $talisman) use ($id) {
                    return !$talisman['is_inactive'] && $talisman->getTagId() === $id;
                })->isNotEmpty()) {
                    $talisman['is_inactive'] = true;
                }

                if ($talismans->filter(static function (SkyBlockItem $talisman) use ($id) {
                    return $talisman->getTagId() === $id;
                })->isNotEmpty()) {
                    $talisman['is_unique'] = false;
                }

                $talismans->push($talisman);
            }

            /**
             * Add inactive talismans from the enderchest and backpacks to the talismans collection
             *
             * @var SkyBlockItem $item
             */
            foreach ($inventory->concat($enderchest) as $index => $item) {
                $items = new Collection([$item]);

                if ($item['type'] !== 'accessory' && isset($item['contains_items'])) {
                    $items = $item['contains_items'];
                }

                foreach ($items->where('type', 'accessory') as $talisman) {
                    $id = $talisman->getTagId();

                    $insertTalisman = clone $talisman; // Otherwise we'd be modifying a reference to the original, and thus the original itself as well.

                    $insertTalisman['is_unique']   = true;
                    $insertTalisman['is_inactive'] = true;

                    if ($talismans->filter(static function (SkyBlockItem $talisman) use ($id) {
                        return $talisman->getTagId() === $id;
                    })->isNotEmpty()) {
                        $insertTalisman['is_unique'] = false;
                    }

                    $talismans->push($insertTalisman);
                }
            }

            /**
             * @link https://github.com/LeaPhant/skyblock-stats/blob/master/src/lib.js#L771
             */
            foreach ($talismans->concat($armor) as $index => $talisman) {
                $id = $talisman->getTagId();

                if (Str::startsWith($id, 'CAMPFIRE_TALISMAN_')) {
                    $tier = (int)Str::afterLast($id, '_');

                    $maxTier = $talismans->filter(static function (SkyBlockItem $talisman) {
                        return Str::startsWith($talisman->getTagId(), 'CAMPFIRE_TALISMAN_');
                    })->map(static function (SkyBlockItem $talisman) {
                        return (int)Str::afterLast($talisman->getTagId(), '_');
                    })->max();

                    if ($tier < $maxTier) {
                        $talisman['is_unique']   = false;
                        $talisman['is_inactive'] = true;
                    }
                }

                if (Str::startsWith($id, 'WEDDING_RING_')) {
                    $tier = (int)Str::afterLast($id, '_');

                    $maxTier = $talismans->filter(static function (SkyBlockItem $talisman) {
                        return Str::startsWith($talisman->getTagId(), 'WEDDING_RING_');
                    })->map(static function (SkyBlockItem $talisman) {
                        return (int)Str::afterLast($talisman->getTagId(), '_');
                    })->max();

                    if ($tier < $maxTier) {
                        $talisman['is_unique']   = false;
                        $talisman['is_inactive'] = true;
                    }
                }

                $talismanUpgradesTable = $this->get('talisman_upgrades');

                if ($talismanUpgradesTable->has($id)) {
                    $talismanUpgrades = new Collection($talismanUpgradesTable->get($id));

                    if ($talismans->filter(static function (SkyBlockItem $talisman) use ($talismanUpgrades) {
                        return !$talisman['is_inactive'] && $talismanUpgrades->contains($talisman->getTagId());
                    })->isNotEmpty()) {
                        $talisman['is_inactive'] = true;
                    }

                    if ($talismans->filter(static function (SkyBlockItem $talisman) use ($talismanUpgrades) {
                        return $talismanUpgrades->contains($talisman->getTagId());
                    })->isNotEmpty()) {
                        $talisman['is_unique'] = false;
                    }
                }

                $talismanDuplicatesTable = $this->get('talisman_duplicates');

                if ($talismanDuplicatesTable->has($id)) {
                    $talismanDuplicates = new Collection($talismanDuplicatesTable->get($id));

                    if ($talismans->filter(static function (SkyBlockItem $talisman) use ($talismanDuplicates) {
                        return $talismanDuplicates->contains($talisman->getTagId());
                    })->isNotEmpty()) {
                        $talisman['is_unique'] = false;
                    }
                }

                /**
                 * New Year Cake Bag health bonus
                 */

                $cakes = [];

                if ($id === 'NEW_YEAR_CAKE_BAG' && isset($talisman['contains_items'])) {
                    $talisman['stats']['health'] = 0;

                    foreach ($talisman['contains_items'] as $item) {
                        if (Arr::has($item, 'tag.ExtraAttributes.new_years_cake') && !in_array($item['tag']['ExtraAttributes']['new_years_cake'], $cakes, true)) {
                            $talisman['stats']['health'] += 1;
                            $cakes[]                     = $item['tag']['ExtraAttributes']['new_years_cake'];
                        }
                    }
                }

                /**
                 * Base name without reforge
                 */

                $talisman['base_name'] = $talisman['display_name'];

                if (Arr::has($talisman, 'tag.ExtraAttributes.modifier')) {
                    $talisman['base_name'] = Str::after($talisman['display_name'], ' ');
                    $talisman['reforge']   = $talisman['tag']['ExtraAttributes']['modifier'];
                }
            }

            $return['talismans'] = $talismans;
            $return['weapons']   = $allItems->filter(static function (SkyBlockItem $item) {
                return $item['type'] === 'sword' || $item['type'] === 'bow';
            });
            $return['rods']      = $allItems->where('type', 'fishing rod');

            foreach ($allItems as $item) {
                if (!isset($item['contains_items'])) {
                    continue;
                }

                $return['weapons']->push(...$item['contains_items']->filter(static function (SkyBlockItem $item) {
                    return $item['type'] === 'sword' || $item['type'] === 'bow';
                }));
                $return['rods']->push(...$item['contains_items']->where('type', 'fishing rod'));
            }

            $return['no_inventory'] = $inventory->isEmpty();

            $return['weapons'] = $return['weapons']->sort(static function ($a, $b) {
                if ($a['rarity'] === $b['rarity']) {
                    if ($b['in_backpack']) {
                        return -1;
                    }

                    return $b['item_index'] - $a['item_index'];
                }

                return array_search($a['rarity'], self::RARITY_ORDER, true) - array_search($b['rarity'], self::RARITY_ORDER, true);
            });

            $return['rods'] = $return['rods']->sort(static function ($a, $b) {
                if ($a['rarity'] === $b['rarity']) {
                    if ($b['in_backpack']) {
                        return -1;
                    }

                    return $b['item_index'] - $a['item_index'];
                }

                return array_search($a['rarity'], self::RARITY_ORDER, true) - array_search($b['rarity'], self::RARITY_ORDER, true);
            });

            $countsOfWeaponId = [];

            /** @var SkyBlockItem $weapon */
            foreach ($return['weapons'] as $weapon) {
                $id = $weapon->getTagId();

                $countsOfWeaponId[$id] = ($countsOfWeaponId[$id] ?? 0) + 1;

                if ($countsOfWeaponId[$id] > 2) {
                    $weapon['hidden'] = true;
                }
            }

            $return['talismans'] = $return['talismans']->sort(static function ($a, $b) {
                $rarityOrder = array_search($a['rarity'], self::RARITY_ORDER, true) - array_search($b['rarity'], self::RARITY_ORDER, true);

                if ($rarityOrder === 0) {
                    if ($a['is_inactive'] === $b['is_inactive']) {
                        return 0;
                    }

                    return $a['is_inactive'] ? 1 : -1;
                }

                return $rarityOrder;
            });

            $swords = $return['weapons']->where('type', 'sword');
            $bows   = $return['weapons']->where('type', 'bow');

            $swordsInInventory = $swords->where('in_backpack', false);
            $bowsInInventory   = $bows->where('in_backpack', false);
            $rodsInInventory   = $return['rods']->where('in_backpack', false);

            if ($swords->isNotEmpty()) {
                $return['highest_rarity_sword'] = $swordsInInventory->filter(static function (SkyBlockItem $item) use ($swordsInInventory) {
                    return $item['rarity'] === $swordsInInventory->first()['rarity'];
                })->sort(static function ($a, $b) {
                    return $a['item_index'] - $b['item_index'];
                })->first();
            }

            if ($bows->isNotEmpty()) {
                $return['highest_rarity_bow'] = $bowsInInventory->filter(static function (SkyBlockItem $item) use ($bowsInInventory) {
                    return $item['rarity'] === $bowsInInventory->first()['rarity'];
                })->sort(static function ($a, $b) {
                    return $a['item_index'] - $b['item_index'];
                })->first();
            }

            if ($return['rods']->isNotEmpty()) {
                $return['highest_rarity_rod'] = $rodsInInventory->filter(static function (SkyBlockItem $item) use ($rodsInInventory) {
                    return $item['rarity'] === $rodsInInventory->first()['rarity'];
                })->sort(static function ($a, $b) {
                    return $a['item_index'] - $b['item_index'];
                })->first();
            }

            $armorWithData = $armor->filter(static function (SkyBlockItem $item) {
                return $item->hasData();
            });

            if ($armorWithData->count() === 1) {
                /** @var SkyBlockItem $armorPiece */
                $armorPiece = $armorWithData->first();

                $return['armor_set']        = $armorPiece['display_name'];
                $return['armor_set_rarity'] = $armorPiece['rarity'];
            } elseif ($armorWithData->count() === 4) {
                $reforgeName = null;

                foreach ($armor as $armorPiece) {
                    $name = $armorPiece['display_name'];

                    if (Arr::has($armorPiece, 'tag.ExtraAttributes.modifier')) {
                        $name = Str::after($name, ' ');
                    }

                    $armorPiece['armor_name'] = $name;
                }

                $reforgedArmorSet = $armor->filter(static function (SkyBlockItem $armorPiece) use ($armor) {
                    return Arr::has($armorPiece, 'tag.ExtraAttributes.modifier') && Arr::has($armor[0], 'tag.ExtraAttributes.modifier') && $armorPiece['tag']['ExtraAttributes']['modifier'] === $armor[0]['tag']['ExtraAttributes']['modifier'];
                });

                if ($reforgedArmorSet->count() === 4) {
                    $reforgeName = Str::before($armor[0]['display_name'], ' ');
                }

                $isMonsterSet = $armor->filter(static function (SkyBlockItem $armorPiece) {
                        return Str::is(['SKELETON_HELMET', 'GUARDIAN_CHESTPLATE', 'CREEPER_LEGGINGS', 'SPIDER_BOOTS', 'TARANTULA_BOOTS'], $armorPiece->getTagId());
                    })->count() === 4;

                $isPerfectSet = $armor->filter(static function (SkyBlockItem $armorPiece) {
                        return Str::startsWith($armorPiece->getTagId(), 'PERFECT_');
                    })->count() === 4;

                if ($isMonsterSet || $armor->filter(static function (SkyBlockItem $armorPiece) use ($armor) {
                        return Str::before($armorPiece['armor_name'], ' ') === Str::before($armor[0]['armor_name'], ' ');
                    })->count() === 4) {
                    $outputName = Str::beforeLast($armor[0]['armor_name'], ' ');

                    if (!Str::endsWith($outputName, 'Armor') && !Str::startsWith($outputName, 'Armor')) {
                        $outputName .= ' Armor';
                    }

                    $return['armor_set']        = $outputName;
                    $return['armor_set_rarity'] = $armor[0]['rarity'];

                    if ($isMonsterSet) {
                        $return['armor_set_rarity'] = 'rare';

                        if ($armor[0]->getTagId() === 'SPIDER_BOOTS') {
                            $return['armor_set'] = 'Monster Hunter Armor';
                        } elseif ($armor[0]->getTagId() === 'TARANTULA_BOOTS') {
                            $return['armor_set'] = 'Monster Raider Armor';
                        }
                    }

                    if ($isPerfectSet) {
                        $sameTier = $armor->filter(static function (SkyBlockItem $armorPiece) use ($armor) {
                            return Str::afterLast($armorPiece->getTagId(), '_') === Str::afterLast($armor[0]->getTagId(), '_');
                        });

                        if ($sameTier->count() === 4) {
                            $return['armor_set'] = 'Perfect Armor - Tier ' . Str::afterLast($armor[0]->getTagId(), '_');
                        } else {
                            $return['armor_set'] = 'Perfect Armor';
                        }
                    }

                    if ($reforgeName !== null) {
                        $return['armor_set'] = $reforgeName . ' ' . $return['armor_set'];
                    }

                }
            }

            return $return;
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/91a03c50f7b0d2ddf0ba50a6f170e1ea8b05fd6f/src/lib.js#L228
         *
         * @param $dataBase64
         *
         * @return Collection|SkyBlockItem[]
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
                $item = new SkyBlockItem($nbtItem);

                $items[] = $item;
            }

            return $items;
        }

        /**
         * Calculate effective health
         *
         * @link https://github.com/LeaPhant/skyblock-stats/
         *
         * @param int $health
         * @param int $defense
         *
         * @return float|int
         */
        protected function getEffectiveHealth(int $health, int $defense) {
            if ($defense <= 0) {
                return $health;
            }

            return round($health * (1 + $defense / 100));
        }

        /**
         * @link https://github.com/LeaPhant/skyblock-stats/blob/9dc08903dff003c059cb52aef3be32ff51e84871/src/lib.js#L594
         *
         * @param Player $player
         * @param string $id
         *
         * @return Collection
         * @throws HypixelFetchException
         * @throws SkyBlockEmptyProfileException
         */
        public static function getSkyBlockMinions(Player $player, string $id): Collection {
            $dataparser     = new self();
            $profileMembers = $dataparser->getSkyBlockProfileMembers($player, $id);

            $minions           = new Collection();
            $craftedGenerators = new Collection();

            foreach ($profileMembers as $member) {
                if (!isset($member['crafted_generators'])) {
                    continue;
                }

                $craftedGenerators->push(...$member['crafted_generators']);
            }

            $minionsTable = $dataparser->get('minions');

            foreach ($craftedGenerators as $generator) {
                $minionLevel = Str::afterLast($generator, '_');
                $minionName  = Str::beforeLast($generator, '_');

                $minion = $minions->where('id', $minionName);

                if ($minion->isEmpty()) {
                    $minions->push(new Collection([
                            'id'        => $minionName,
                            'max_level' => 0,
                            'levels'    => new Collection([$minionLevel])
                        ] + ($minionsTable[$minionName] ?? [])));
                } else {
                    $minion->first()['levels']->push($minionLevel);
                }
            }

            foreach ($minionsTable as $minionName => $minion) {
                if ($minions->where('id', $minionName)->isEmpty()) {
                    $minions->push(new Collection([
                            'id'        => $minionName,
                            'max_level' => 0,
                            'levels'    => new Collection()
                        ] + $minion));
                }
            }

            foreach ($minions as $minion) {
                /** @var Collection $levels */
                $levels              = $minion['levels'];
                $minion['levels']    = $levels->sort()->unique();
                $minion['max_level'] = (int)($minion['levels']->max() ?? 0);

                if (!isset($minion['name'])) {
                    $minion['name'] = ucfirst(strtolower($minion['id']));
                }

                $minion['texture_name'] = isset($minion['head']) ? Str::afterLast($minion['head'], '/head/') : 'bc8ea1f51f253ff5142ca11ae45193a4ad8c3ab5e9c6eec8ba7a4fcb7bac40';
            }

            return $minions;
        }

        /**
         * @param Player $player
         * @param string $id
         *
         * @return array
         * @throws HypixelFetchException
         * @throws SkyBlockEmptyProfileException
         */
        public function getSkyBlockProfileMembers(Player $player, string $id): array {
            $skyBlockProfile = $player->getHypixelPHP()->getSkyBlockProfile($id);

            if ($skyBlockProfile === null) {
                throw new HypixelFetchException('SkyBlock profile for user ' . $player->getUUID() . ' is null');
            }

            $members = $skyBlockProfile->getMembers();

            if ($members === null) {
                throw new SkyBlockEmptyProfileException('SkyBlock profile ' . $id . ' is empty');
            }

            return $members;
        }

        /**
         * @param Player $player
         * @param string $id
         *
         * @return Collection
         * @throws HypixelFetchException
         * @throws SkyBlockEmptyProfileException
         * @noinspection PhpDocRedundantThrowsInspection
         */
        public static function getSkyBlockPets(Player $player, string $id): Collection {
            return Cache::remember('skyblock.profile.' . $player->getUUID() . '.' . $id . '.pets', config('cache.times.skyblock_profile'), function () use ($id, $player) {
                $dataparser = new self();
                return $dataparser->getSkyBlockProfile($player, $id)->get('pets');
            });
        }

        /**
         * @return Collection
         */
        public function getConstants(): Collection {
            return $this->constants;
        }
    }
