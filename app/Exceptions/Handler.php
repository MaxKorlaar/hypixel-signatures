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

    namespace App\Exceptions;

    use App\Http\Controllers\Signatures\BaseSignature;
    use Exception;
    use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Support\Facades\Request;
    use Illuminate\Support\Facades\Response;
    use Illuminate\Support\Str;
    use Log;
    use MarvinLabs\DiscordLogger\Discord\Exceptions\MessageCouldNotBeSent;
    use Plancke\HypixelPHP\exceptions\BadResponseCodeException;
    use Plancke\HypixelPHP\exceptions\CurlException;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Throwable;

    /**
     * Class Handler
     *
     * @package App\Exceptions
     */
    class Handler extends ExceptionHandler {
        /**
         * A list of the exception types that are not reported.
         *
         * @var array
         */
        protected $dontReport = [
            HypixelFetchException::class,
            SkyBlockEmptyProfileException::class,
            MessageCouldNotBeSent::class
        ];

        /**
         * A list of the inputs that are never flashed for validation exceptions.
         *
         * @var array
         */
        protected $dontFlash = [
            'password',
            'password_confirmation',
        ];

        /**
         * Report or log an exception.
         *
         *
         * @return void
         * @throws Exception
         * @throws Throwable
         */
        #[\Override]
        public function report(Throwable $exception) {
            if (($exception instanceof BadResponseCodeException) && $exception->getActualCode() === 429) {
                Log::stack(['daily'])->error($exception->getMessage(), ['url' => Request::url(), 'data' => Request::except('_token')]);
                return;
            }

            if ($exception instanceof CurlException && Str::contains($exception->getMessage(), ['Operation timed out after'])) {
                Log::stack(['daily'])->error($exception->getMessage(), ['url' => Request::url()]);
                return;
            }

            parent::report($exception);
        }

        /**
         * Render an exception into an HTTP response.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
         * @throws Throwable
         */
        #[\Override]
        public function render($request, Throwable $exception) {
            if ($exception instanceof BadResponseCodeException) {
                return Response::view('errors.hypixel_api', [
                    'exception' => $exception,
                    'code'      => $exception->getActualCode()
                ]);
            }

            if (($exception instanceof NotFoundHttpException) && $request->prefers(['text', 'application', 'image']) === 'image') {
                return BaseSignature::generateErrorImage(trans('error.image.404.text'), $exception->getStatusCode(), 300, 200, trans('error.image.404.title'));
            }

            return parent::render($request, $exception);
        }
    }
