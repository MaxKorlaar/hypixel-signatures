<?php

    namespace App\Http\Controllers\Signatures;

    use App\Http\Controllers\Controller;
    use GDText\Box;
    use GDText\Color;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Image;
    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\exceptions\InvalidUUIDException;
    use Plancke\HypixelPHP\HypixelPHP;
    use Plancke\HypixelPHP\log\impl\NoLogger;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class BaseSignature
     *
     * @package App\Http\Controllers\Signatures
     */
    abstract class BaseSignature extends Controller {

        private const FULLY_TRANSPARENT = 127;

        /**
         * @var HypixelPHP $api
         */
        protected $api;

        /**
         * BaseSignature constructor.
         *
         * @throws HypixelPHPException
         */
        public function __construct() {
            $this->api = new HypixelPHP(config('signatures.api_key'));
            $this->api->setLogger(new NoLogger($this->api));
            $this->api->getCacheHandler()->setBaseDirectory(storage_path('app/cache/hypixelphp'));
            $this->api->getFetcher()->setTimeOut(config('signatures.api_timeout'));
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
                $player = $this->getPlayerByUuid($uuid);

                /** @var HypixelObject $player */
                if (($player instanceof HypixelObject) && $player->getResponse() !== null && !$player->getResponse()->wasSuccessful()) {
                    return $this->generateErrorImage("Bad API response.\n{$player->getResponse()->getData()['cause']}");
                }

                if ($player instanceof Player) {
                    if (empty($player->getData())) {
                        return $this->generateErrorImage('Player has no public data.');
                    }
                    return $player;
                }

                return $this->generateErrorImage('Unexpected API response.');
            } catch (InvalidUUIDException $exception) {
                return $this->generateErrorImage('UUID is invalid.');
            } catch (HypixelPHPException $e) {
                return $this->generateErrorImage('Unknown: ' . $e->getMessage());
            }
        }

        /**
         * @param string $uuid
         *
         * @return HypixelObject|\Plancke\HypixelPHP\fetch\Response|Player|null
         * @throws HypixelPHPException
         */
        protected function getPlayerByUuid(string $uuid) {
            return $this->api->getPlayer(['uuid' => $uuid]);
        }

        /**
         * @param     $error
         * @param int $width
         * @param int $height
         *
         * @return Response
         */
        protected function generateErrorImage($error, $width = 740, $height = 160): Response {
            $image = $this->getImage($width, $height);
            $box   = new Box($image);
            $box->setFontFace(resource_path('fonts/SourceSansPro/SourceSansPro-Light.otf'));
            $box->setFontColor(new Color(255, 0, 0));
            $box->setFontSize($height / 3);
            $box->setBox(10, 10, $width - 10, $height - 10);
            $box->setTextAlign('center', 'top');
            $box->draw('Something went wrong');

            $box->setBox(10, $height / 3 + 15, $width - 10, $height - 10);
            $box->setFontSize($height / 4);
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
        protected function getImage($width, $height) {
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

            $watermarkBoundingBox = imagettfbbox(16, 0, $font, config('signatures.watermark'));
            imagettftext($image, $size, 0, $imageWidth - $watermarkBoundingBox[4], $imageHeight - $watermarkBoundingBox[3], $grey, $font, config('signatures.watermark'));
        }
    }
