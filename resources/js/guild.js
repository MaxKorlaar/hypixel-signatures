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
import { createApp } from 'vue';
import mitt from 'mitt';
import SkyWarsTable from "./guild/SkyWarsTable";
import MembersTable from "./guild/MembersTable";
import BedWarsTable from "./guild/BedWarsTable";
import TNTGamesTable from "./guild/TNTGamesTable";
import MegaWallsTable from "./guild/MegaWallsTable";
import GeneralStatisticsTable from "./guild/GeneralStatisticsTable";
import MurderMysteryTable from "./guild/MurderMysteryTable";
import formatTime from "./guild/_filters/timeFormat";

const axios = require('axios').default;

const emitter = mitt();

const app = createApp({
    components: {SkyWarsTable, MembersTable, BedWarsTable, TNTGamesTable, MegaWallsTable, MurderMysteryTable, GeneralStatisticsTable},
    data() {
        return {
            members: [],
            meta:    {
                total_members: 0,
                loaded:        0
            },
            urls:    {
                get_members: ''
            },
            loading: true
        }
    },
    methods:    {
        getMembersInterval() {
            if (this.meta.loaded < this.meta.total_members) {
                let previousCount = this.meta.loaded;

                this.getMembers().finally(() => {
                    setTimeout(() => {
                        this.getMembersInterval();
                    }, this.meta.loaded === previousCount ? 2500 : 750);
                });
            }
        },
        getMembers() {
            this.loading = true;

            return axios.get(this.urls.get_members).then(response => {
                const data = response.data;

                this.members = data.members;
                this.meta    = data.meta;
            }).catch(error => {
                console.error(error);
            }).finally(() => {
                this.loading = false;
            });
        },
    },
    mounted() {
        this.members = window.Paniek.members;
        this.meta    = window.Paniek.meta;
        this.urls    = window.Paniek.urls;

        this.loading = false;

        setTimeout(() => {
            this.getMembersInterval();
        }, 1000);

    }
});

app.config.globalProperties.$filters = {
    number_format(value) {
        if (isNaN(value)) return value;
        return (new Intl.NumberFormat()).format(value);
    },
    time_format: formatTime
};

app.provide('emitter', emitter);
app.mount('#guild-members-app');
