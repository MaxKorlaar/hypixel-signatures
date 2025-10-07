/*
 * Copyright (c) 2021-2024 Max Korlaar
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

const mix = require('laravel-mix');
require('laravel-mix-purgecss');
require('laravel-mix-bundle-analyzer');
const MomentLocalesPlugin = require("moment-locales-webpack-plugin");

if (mix.isWatching()) {
    mix.bundleAnalyzer();
}

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/signatures.js', 'public/js')
    .js('resources/js/friends.js', 'public/js')
    .js('resources/js/guild.js', 'public/js')
    .js('resources/js/status.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .version()
    .vue({version: 3})
    .extract(['vue', 'axios'])
    .purgeCss()
    .webpackConfig({
        plugins: [
            new MomentLocalesPlugin({
                localesToKeep: ['nl', 'de'],
            }),
        ]
    })
    .browserSync({
        proxy: 'hypixel-signatures.test',
        files: [
            "resources/views/**/*.twig",
            'app/**/*.php',
            'public/js/**/*.js',
            'public/css/**/*.css'
        ],
        // snippetOptions: {
        //     // Provide a custom Regex for inserting the snippet.
        //     rule: {
        //         match: /<\/body>/i,
        //         fn:    function (snippet, match) {
        //             return snippet + match;
        //         }
        //     }
        // }
    });
