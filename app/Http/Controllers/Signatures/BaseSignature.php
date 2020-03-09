<?php

    namespace App\Http\Controllers\Signatures;

    use App\Http\Controllers\Controller;
    use App\Utilities\HypixelAPI;
    use GDText\Box;
    use GDText\Color;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\exceptions\InvalidUUIDException;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class BaseSignature
     *
     * @package App\Http\Controllers\Signatures
     */
    abstract class BaseSignature extends Controller {

        private const FULLY_TRANSPARENT = 127;

        /**
         * @var HypixelAPI $api
         */
        protected $api;

        /**
         * BaseSignature constructor.
         *
         */
        public function __construct() {
            $this->api = new HypixelAPI();
        }

        /**
         * @param Request $request
         * @param string  $uuid
         *
         * @return Response
         */
        public function render(Request $request, string $uuid): Response {

            $playerOrResponse = $this->getPlayerData($uuid);
            if ($playerOrResponse instanceof Player) {
                return $this->signature($request, $playerOrResponse);
            }

            return $playerOrResponse;
        }

        /**
         * @param string $uuid
         *
         * @return Response|Player
         */
        protected function getPlayerData(string $uuid) {
            try {
                $player = $this->api->getPlayerByUuid($uuid);

                /** @var HypixelObject $player */
                if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                    return self::generateErrorImage("Bad API response.\n{$player->getResponse()->getData()['cause']}");
                }

                if ($player instanceof Player) {
                    if (empty($player->getData())) {
                        return self::generateErrorImage('Player has no public data.');
                    }
                    return $player;
                }

                return self::generateErrorImage('Unexpected API response.');
            } catch (InvalidUUIDException $exception) {
                return self::generateErrorImage('UUID is invalid.');
            } catch (HypixelPHPException $e) {
                return self::generateErrorImage('Unknown: ' . $e->getMessage());
            }
        }

        /**
         * @param     $error
         * @param int $width
         * @param int $height
         *
         * @return Response
         */
        public static function generateErrorImage($error, $width = 740, $height = 160): Response {
            $image = self::getImage($width, $height);
            $box   = new Box($image);
            $box->setFontFace(resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf'));
            $box->setFontColor(new Color(255, 0, 0));
            $box->setFontSize($height / 3);
            $box->setBox(5, 0, $width - 5, $height - 5);
            $box->setTextAlign('center', 'top');
            $box->draw('Something went wrong');

            $box->setBox(5, $height / 4 + 15, $width - 5, $height - 5);
            $box->setFontSize($height / 5);
            $box->setFontColor(new Color(0, 0, 0));
            $box->setTextShadow(new Color(0, 0, 0, 50), 1, 1);
            $box->draw($error);

            return Image::make($image)->response('png');
        }

        /**
         * @param $width
         * @param $height
         *
         * @return false|resource
         */
        protected static function getImage($width, $height) {
            $image       = imagecreatetruecolor($width, $height);
            $transparent = imagecolorallocatealpha($image, 250, 100, 100, config('app.debug') ? 0 : self::FULLY_TRANSPARENT);
            imagefill($image, 0, 0, $transparent);
            imagesavealpha($image, true);
            return $image;
        }

        /**
         * @param Request $request
         * @param Player  $player
         *
         * @return Response
         */
        abstract protected function signature(Request $request, Player $player): Response;

        /**
         * Add a watermark referencing the site's name in the bottom right of the supplied image
         *
         * @param     $image
         * @param     $font
         * @param     $imageWidth
         * @param     $imageHeight
         * @param int $size
         */
        protected function addWatermark($image, $font, $imageWidth, $imageHeight, $size = 16): void {
            $grey = imagecolorallocate($image, 203, 203, 203);

            $watermarkBoundingBox = imagettfbbox($size, 0, $font, config('signatures.watermark'));
            imagettftext($image, $size, 0, $imageWidth - $watermarkBoundingBox[4], $imageHeight - $watermarkBoundingBox[3], $grey, $font, config('signatures.watermark'));
        }
    }
