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

    namespace App\Http\Controllers\Guild;

    use App\Exceptions\HypixelFetchException;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\Guild\ViewInfoByUsernameOrGuildNameRequest;
    use App\Utilities\HypixelAPI;
    use App\Utilities\MinecraftAvatar\MojangAPI;
    use Cache;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\RateLimiter;
    use Illuminate\Support\Facades\Redis;
    use Illuminate\Support\Str;
    use Illuminate\View\View;
    use JsonException;
    use Plancke\HypixelPHP\classes\gameType\GameTypes;
    use Plancke\HypixelPHP\color\ColorUtils;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\guild\Guild;
    use Plancke\HypixelPHP\responses\player\Player;
    use Psr\SimpleCache\InvalidArgumentException;

    /**
     * Class GuildController
     *
     * @package App\Http\Controllers\Guild
     */
    class GuildController extends Controller {
        public function getIndex(): View {
            $recentlyViewed = new Collection(
                Redis::connection('cache')
                    ->zRevRangeByScore('recent_guilds', '+inf', '0', [
                        'withscores' => true, 'limit' => [0, 20]
                    ])
            )->map(static fn($value, $key) => ['id' => $key, 'views' => $value] + Cache::get('recent_guilds.' . $key, ['name' => $key]));

            return view('guild.index', [
                'recently_viewed' => $recentlyViewed
            ]);
        }

        /**
         *
         * @throws JsonException
         * @throws HypixelPHPException
         * @throws InvalidArgumentException
         */
        public function redirectToInfo(ViewInfoByUsernameOrGuildNameRequest $request): RedirectResponse {
            if ($request->input('username') !== null) {
                $mojangAPI = new MojangAPI();
                $data      = $mojangAPI->getUUID($request->input('username'));

                if (!$data['success']) {
                    if ($data['status_code'] === 204) {
                        return back()->withInput()->withErrors([
                            'username' => 'This username does not exist'
                        ]);
                    }

                    return back()->withInput()->withErrors([
                        'username' => ($data['throttle'] ?? false) ? 'We\'re trying to use Mojang\'s API a bit too much right now, please try again later' : 'An unknown error has occurred while trying to retrieve your UUID from Mojang\'s servers'
                    ]);
                }

                $uuid = $data['data']['id'];

                $HypixelAPI = new HypixelAPI();

                $guild = $HypixelAPI->getGuildByPlayerUuid($uuid);
            } else {
                return back()->withInput()->withErrors(['name' => 'This feature is not supported yet']);
            }

            if ($guild instanceof Guild) {
                return redirect()->route('guild.info', [$guild->getName()]);
            }

            return back(302, [], route('friends'))->withInput()->withErrors([
                'username' => 'An unknown error has occurred while trying to fetch the guild for ' . $data['data']['name'] ?? '. They might not be in a guild at the moment'
            ]);
        }

        /**
         *
         * @return RedirectResponse|View
         * @throws HypixelPHPException
         * @throws HypixelFetchException
         * @throws InvalidArgumentException
         */
        public function getInfo(Request $request, string $nameOrId) {
            $HypixelAPI = new HypixelAPI();

            if (HypixelAPI::isValidMongoId($nameOrId)) {
                $guild = $HypixelAPI->getGuildById($nameOrId);
            } else {
                $guild = $HypixelAPI->getGuildByName($nameOrId);
            }

            if ($guild instanceof Guild) {
                if (empty($guild->getData())) {
                    return redirect()->route('guild')->withErrors(['username' => 'This guild does not exist']);
                }

                RateLimiter::attempt(
                    "increase_recent_guilds_views:{$request->ip()}:{$guild->getID()}",
                    1,
                    static function () use ($guild) {
                        Redis::connection('cache')->zIncrBy('recent_guilds', 1, $guild->getID());
                    },
                    config('cache.times.recents_decay')
                );

                Cache::set('recent_guilds.' . $guild->getID(), [
                    'name'         => $guild->getName(),
                    'id'           => $guild->getID(),
                    'member_count' => $guild->getMemberCount()
                ], config('cache.times.recent_guilds'));

                $memberList = $guild->getMemberList()->getList();

                uksort($memberList, static function ($a, $b) {
                    if (Str::is(['guildmaster', 'guild master'], strtolower($b))) {
                        return 1;
                    }

                    return 0;
                });

                $highestRank = array_shift($memberList);

                $guildMaster       = $highestRank[0];
                $guildMasterPlayer = $guildMaster->getPlayer();

                if (!$guildMasterPlayer instanceof Player) {
                    throw new HypixelFetchException('An unknown error has occurred while trying to fetch the guildmaster of ' . $guild->getName());
                }

                $preferredGames = new Collection($guild->getPreferredGames())->map(static function ($gameName) {
                    $gameType = GameTypes::fromEnum($gameName);

                    if ($gameType === null) {
                        return ucfirst(strtolower($gameName));
                    }

                    return $gameType->getName();
                });

                $games = new Collection($guild->getExpByGameType());

                $mostActiveGames = $games->sortDesc()->slice(0, 5)->map(static function ($xp, $gameName) {
                    $gameType = GameTypes::fromEnum($gameName);

                    if ($gameType === null) {
                        return ucfirst(strtolower($gameName));
                    }

                    return $gameType->getName();
                });

                return view('guild.info', [
                    'guild'             => $guild,
                    'guildmaster'       => $guildMasterPlayer,
                    'guildmaster_name'  => ColorUtils::getColorParser()->parse($guildMasterPlayer->getRawFormattedName()),
                    'formatted_tag'     => ColorUtils::getColorParser()->parse($guild->getTagColor() . $guild->getTag()),
                    'preferred_games'   => $preferredGames,
                    'most_active_games' => $mostActiveGames,
                    'description' => ColorUtils::getColorParser()->parse(preg_replace('/([&§]([0-9A-FK-ORa-fk-or]))/iu', ColorUtils::COLOR_CHAR . "\$2", $this->linkify(e($guild->getDescription()))))
                ]);
            }

            throw new HypixelFetchException('An unknown error has occurred while trying to fetch this Guild from Hypixel. It might not exist or has been renamed');
        }

        /**
         * Turn all URLs in clickable links.
         *
         * @param string $value
         * @param array  $protocols http/https, ftp, mail, twitter
         *
         * @link https://gist.github.com/jasny/2000705
         *
         */
        public function linkify($value, $protocols = ['http', 'mail'], array $attributes = ['target' => '_blank']): string {
            // Link attributes
            $attr = '';
            foreach ($attributes as $key => $val) {
                $attr .= ' ' . $key . '="' . htmlentities((string) $val) . '"';
            }

            $links = [];

            // Extract existing links and tags
            $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~is', static function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);

            // Extract text links for each protocol
            foreach ((array)$protocols as $protocol) {
                $value = match ($protocol) {
                    'http', 'https' => preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![.,:])~i', static function ($match) use ($protocol, &$links, $attr) {
                        if ($match[1] !== '' && $match[1] !== '0') {
                            $protocol = $match[1];
                        }
                        $link = $match[2] ?: $match[3];
                        return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$link</a>") . '>';
                    }, (string) $value),
                    'mail' => preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![.,:])~', static function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>'; }, (string) $value),
                    default => preg_replace_callback('~' . preg_quote((string) $protocol, '~') . '://([^\s<]+?)(?<![.,:])~i', static function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>'; }, (string) $value),
                };
            }

            // Insert all link
            return preg_replace_callback('/<(\d+)>/', static function ($match) use (&$links) { return $links[$match[1] - 1]; }, (string) $value);
        }
    }
