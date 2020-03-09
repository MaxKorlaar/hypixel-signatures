<?php

    namespace App\Utilities;

    use Plancke\HypixelPHP\classes\HypixelObject;
    use Plancke\HypixelPHP\exceptions\HypixelPHPException;
    use Plancke\HypixelPHP\fetch\Response;
    use Plancke\HypixelPHP\HypixelPHP;
    use Plancke\HypixelPHP\log\impl\NoLogger;
    use Plancke\HypixelPHP\responses\player\Player;

    /**
     * Class HypixelAPI
     *
     * @package App\Utilities
     */
    class HypixelAPI {
        /**
         * @var HypixelPHP $api
         */
        protected $api;

        /**
         * HypixelAPI constructor.
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
         * @param string $uuid
         *
         * @return HypixelObject|Response|Player|null
         * @throws HypixelPHPException
         */
        public function getPlayerByUuid(string $uuid) {
            return $this->api->getPlayer(['uuid' => $uuid]);
        }

        /**
         * @return HypixelPHP
         */
        public function getApi(): HypixelPHP {
            return $this->api;
        }
    }
