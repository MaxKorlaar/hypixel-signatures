/*
 * Copyright (c) 2020-2025 Max Korlaar
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
import Vue from 'vue';
import * as moment from "moment";

const axios = require('axios').default;

// noinspection ObjectAllocationIgnored
new Vue({
    el:       '#player-status-app',
    data:     {
        urls:    {
            get_status: ''
        },
        loading: true,
        status:  null,
        player:  {}
    },
    methods:  {
        getStatusInterval() {
            this.getStatus().finally(() => {
                setTimeout(() => {
                    this.getStatusInterval();
                }, 30 * 1000);
            });
        },
        getStatus() {
            this.loading = true;

            return axios.get(this.urls.get_status).then(response => {
                const data = response.data;

                this.player = data.player;
                this.status = data.status;
            }).catch(error => {
                console.error(error);
            }).finally(() => {
                this.loading = false;
            });
        }
    },
    watch:    {},
    computed: {
        last_seen() {
            return moment(this.player.last_seen, undefined, window.Paniek.language).fromNow();
        }
    },
    mounted() {
        this.player = window.Paniek.player;
        this.status = window.Paniek.status;
        this.urls   = window.Paniek.urls;

        this.loading = false;

        setTimeout(() => {
            this.getStatusInterval();
        }, 30 * 1000);
    }
});
