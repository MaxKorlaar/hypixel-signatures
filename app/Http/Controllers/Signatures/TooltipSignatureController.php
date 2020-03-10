<?php

    namespace App\Http\Controllers\Signatures;

    use App\Utilities\ColourHelper;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class TooltipSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    final class TooltipSignatureController extends BaseSignature {

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         */
        protected function signature(Request $request, Player $player): Response {
            $image = imagecreatefrompng(resource_path('images/Tooltip.png'));

            $fontMinecraftia = resource_path('fonts/minecraftia/Minecraftia.ttf');
            $green           = imagecolorallocate($image, 85, 255, 85);

            $rank       = $player->getRank(false);
            $rankPrefix = $rank->getPrefix($player);

            $rankPrefix = substr($rankPrefix, 0, 3) . substr($rankPrefix, 4, -1);

            imagettftext($image, 14, 0, 10, 25, $green, $fontMinecraftia, 'Character Information');

            $spacing  = 22;
            $start    = 50 - 16;
            $fontSize = 13;

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start, '§7Rank: ' . $rankPrefix); // Rank

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start + $spacing, '§7Level: §6' . ($player->getLevel())); // Level

            $level     = $player->getLevel();
            $expNeeded = ($level - 1) * 2500 + 10000;
            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start + 2 * $spacing, '§7Experience until next Level: §6' . $expNeeded); // Experience until next level

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start + 3 * $spacing, '§7Hypixel Credits: §b' . $player->getInt('vanityTokens')); // Hypixel Credits

            ColourHelper::minecraftStringToTTFText($image, $fontMinecraftia, $fontSize, 10, $start + 4 * $spacing, '§7Karma: §d' . $player->getInt('karma')); // Karma

            return Image::make($image)->response('png');
        }

    }
