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

use Spatie\DirectoryCleanup\Policies\DeleteEverything;

    return [

        'directories'    => [

            /*
             * Here you can specify which directories need to be cleanup. All files older than
             * the specified amount of minutes will be deleted.
             */

            storage_path('app/public/minecraft-avatars') => [
                'deleteAllOlderThanMinutes' => 60 * 24 * 14
            ],

            storage_path('app/cache') => [
                'deleteAllOlderThanMinutes' => 60 * 24 * 30
            ]

            /*
            'path/to/a/directory' => [
                'deleteAllOlderThanMinutes' => 60 * 24,
            ],
            */
        ],

        /*
         * If a file is older than the amount of minutes specified, a cleanup policy will decide if that file
         * should be deleted. By default every file that is older that the specified amount of minutes
         * will be deleted.
         *
         * You can customize this behaviour by writing your own clean up policy.  A valid policy
         * is any class that implements `Spatie\DirectoryCleanup\Policies\CleanupPolicy`.
         */
        'cleanup_policy' => DeleteEverything::class,
    ];
