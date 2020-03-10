<?php

    namespace App\Http\Controllers;

    use App\Http\Controllers\Signatures\BaseSignature;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Response;
    use Illuminate\Routing\Redirector;

    /**
     * Class RedirectOldSignaturesController
     *
     * @package App\Http\Controllers
     */
    class RedirectOldSignaturesController extends Controller {
        private const URL_MAPPING = [
            'Main'       => 'signatures.general',
            'Main-small' => 'signatures.general_small',
            'Tooltip'    => 'signatures.general_tooltip'
        ];

        /**
         * @param      $oldSignatureName
         * @param      $uuid
         * @param null $other
         *
         * @return Application|RedirectResponse|Response|Redirector
         */
        public function redirect($oldSignatureName, $uuid, $other = null) {
            if (isset(self::URL_MAPPING[$oldSignatureName])) {
                return redirect(route(self::URL_MAPPING[$oldSignatureName], [$uuid]), 301);
            }

            return BaseSignature::generateErrorImage("The signature '{$oldSignatureName}' does not exist anymore or has been moved. See Hypixel.Paniek.de.");
        }
    }
