/*
 * Copyright (c) 2020-2022 Max Korlaar
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
import Ads from 'vue-google-adsense';
import vue_script2 from "vue-script2";

Vue.use(vue_script2);
Vue.use(Ads.InFeedAdsense);

const axios = require('axios').default;

// noinspection ObjectAllocationIgnored
new Vue({
    el:       '#friends-list-app',
    data:     {
        friends:               [],
        meta:                  {
            total_friends: 0,
            loaded:        0
        },
        urls:                  {
            get_friends: ''
        },
        loading:               true,
        visible_friends_count: 150
    },
    methods:  {
        getFriendsInterval() {
            if (this.meta.loaded < this.meta.total_friends) {
                let previousCount = this.meta.loaded;

                this.getFriends().finally(() => {
                    setTimeout(() => {
                        this.getFriendsInterval();
                    }, this.meta.loaded === previousCount ? 2500 : 750);
                });
            }
        },
        getFriends() {
            this.loading = true;

            return axios.get(this.urls.get_friends).then(response => {
                const data = response.data;

                this.friends = data.friends;
                this.meta    = data.meta;
            }).catch(error => {
                console.error(error);
            }).finally(() => {
                this.loading = false;
            });
        }
    },
    watch:    {},
    computed: {},
    mounted() {
        this.friends = window.Paniek.friends;
        this.meta    = window.Paniek.meta;
        this.urls    = window.Paniek.urls;

        this.loading = false;

        setTimeout(() => {
            this.getFriendsInterval();
        }, 1000);

        window.addEventListener('scroll', () => {
            if ((document.body.clientHeight - window.innerHeight - 100) < window.scrollY && this.visible_friends_count <= this.friends.length) {
                this.visible_friends_count += 50;
            }
        }, {
            passive: true
        });
    }
});
