<?php

    namespace App\Http\Controllers\Signatures;

    use App\Utilities\ColourHelper;
    use App\Utilities\MinecraftAvatar\ThreeDAvatar;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class SmallGeneralSignatureController
     *
     * @package App\Http\Controllers\Signatures
     */
    final class SmallGeneralSignatureController extends GeneralSignatureController {

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         * @throws HypixelPHPException
         */
        protected function signature(Request $request, Player $player): Response {
            $image = BaseSignature::getImage(630, 100);
            [$black, $purple, $blue] = self::getColours($image);
            $fontSourceSansProLight = resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf');

            $karma        = $player->get('karma', 0);
            $vanityTokens = $player->get('vanityTokens', 0);

            if ($request->has('no_3d_avatar')) {
                $avatarWidth = 0;
                $textX       = $avatarWidth + 4;
            } else {
                $threedAvatar = new ThreeDAvatar();
                $avatarImage  = $threedAvatar->getThreeDSkinFromCache($player->getUUID(), 3, 30, false, true, true);

                $avatarWidth = imagesx($avatarImage);
                $textX       = $avatarWidth + 4;

                imagecopy($image, $avatarImage, 0, 0, 0, 0, imagesx($avatarImage), imagesy($avatarImage));
                imagedestroy($avatarImage);
            }

            ColourHelper::minecraftStringToTTFText($image, $fontSourceSansProLight, 21, $textX, 5, $player->getRawFormattedName(true, $request->has('guildTag')));

            $linesY = [50, 75]; // Y starting points of the various text lines

            imagettftext($image, 19, 0, $textX, $linesY[0], $blue, $fontSourceSansProLight, $vanityTokens . ' Hypixel Credits'); // Hypixel Credits

            imagettftext($image, 19, 0, $textX, $linesY[1], $purple, $fontSourceSansProLight, $karma . ' karma'); // Amount of karma

            imagettftext($image, 19, 0, 315, $linesY[0], $black, $fontSourceSansProLight, 'Level ' . $player->getLevel()); // Network level

            imagettftext($image, 19, 0, 315, $linesY[1], $black, $fontSourceSansProLight, 'Daily Reward High Score: ' . $player->getInt('rewardHighScore')); // Daily reward high score

            $this->addWatermark($image, $fontSourceSansProLight, 630, 100, 14); // Watermark/advertisement

            return Image::make($image)->response('png');
        }

    }
