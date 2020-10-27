/*
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
import Vue from 'vue';
import Ads from 'vue-google-adsense';
import vue_script2 from "vue-script2";

Vue.use(vue_script2);
Vue.use(Ads.InFeedAdsense);

const axios = require('axios').default;

// noinspection ObjectAllocationIgnored
new Vue({
    el:       '#signature-app',
    data:     {
        signatures:              {},
        current_signature_group: {},
        urls:                    {
            get_uuid:              '',
            get_profile:           '',
            get_skyblock_profiles: '',
        },
        selected_signature:      null,
        username:                null,
        uuid:                    null,
        loading:                 true,
        errors:                  {},
        skyblock: {
            profile:  null,
            profiles: [],
            loading:  true
        }
    },
    methods:  {
        getUuidFromUsername() {
            this.username        = this.username.trim().replace(/-/g, '');
            window.location.hash = this.username;

            if (/^[0-9a-f]{32}$/i.test(this.username)) {
                this.uuid = this.username;
                this.getUsernameFromUuid();
            } else {
                this.loading = true;
                axios.get(this.urls.get_uuid.replace(':username', this.username)).then(response => {
                    const data = response.data;

                    if (data.success) {
                        this.uuid            = data.data.id;
                        this.username        = data.data.name;
                        window.location.hash = data.data.name;
                    } else {
                        if (data.throttle) {
                            this.errors.username = "Unfortunately, we're using Mojang's API a bit too much right now. Please try again in a minute.";
                            return;
                        } else if (data.status_code === 204) {
                            this.errors.username = 'This username could not be found.';
                            // Username does not exist
                            return;
                        }

                        this.errors.username = 'Unfortunately, something has went wrong while fetching your UUID. Please try again later.';
                    }
                }).catch(error => {
                    console.error(error);
                    this.loading = false;
                }).finally(() => {
                    this.loading = false;
                });
            }
        },

        getUsernameFromUuid() {
            this.loading = true;
            axios.get(this.urls.get_profile.replace(':uuid', this.uuid)).then(response => {
                const data = response.data;

                if (data.success) {
                    this.username        = data.data.username;
                    window.location.hash = data.data.username;
                } else {
                    if (data.throttle) {
                        this.errors.username = "Unfortunately, we're using Mojang's API a bit too much right now. Please try again in a minute.";
                        return;
                    } else if (data.status_code === 204) {
                        this.errors.username = 'This UUID does not exist.';
                        return;
                    }

                    this.errors.username = 'Unfortunately, something has went wrong while fetching your profile. Please try again later.';
                }
            }).catch(error => {
                console.error(error);
            }).finally(() => {
                this.loading = false;
            });
        },

        getUuidOrFallback() {
            return this.uuid ? this.uuid : 'b876ec32e396476ba1158438d83c67d4';
        },

        replaceParameters(text) {
            text = text.replace(':uuid', this.getUuidOrFallback);

            if (this.skyblock.profile !== null) {
                text = text.replace(':skyblock_profile', this.skyblock.profile.profile_id);
            }

            return text;
        },

        getImageUrl(signature) {
            return this.replaceParameters(signature.url);
        },

        getPreviewImageUrl(signature) {
            return this.getImageUrl(signature);
        },

        clearError(key) {
            this.errors[key] = null;
        },

        getSkyBlockProfiles() {
            this.loading          = true;
            this.skyblock.loading = true;

            axios.get(this.urls.get_skyblock_profiles.replace(':uuid', this.getUuidOrFallback)).then(response => {
                const data = response.data;

                if (data.success) {
                    this.skyblock.profiles = data.profiles;

                    if (data.profiles.length > 0) {
                        this.skyblock.profile = this.skyblock.profiles[0];
                    }
                } else {
                    this.errors.skyblock_profiles = 'Unfortunately, something has went wrong while fetching your SkyBlock profiles. Please try again later.';
                }
            }).catch(error => {
                console.error(error);
            }).finally(() => {
                this.loading          = false;
                this.skyblock.loading = false;
            });
        }
    },
    watch:    {
        selected_signature(signature) {
            gtag('event', 'generate', {
                'event_category': 'signature',
                'event_label':    signature.name
            });
        },
        current_signature_group(signatureGroup) {
            if (signatureGroup.short_name === 'SkyBlock') {
                if (this.skyblock.profiles.length === 0) {
                    this.getSkyBlockProfiles();
                }
            }
        },

        uuid() {
            this.skyblock.profile  = null;
            this.skyblock.profiles = [];

            if (this.current_signature_group.short_name === 'SkyBlock') {
                this.getSkyBlockProfiles();
            }
        }

    },
    computed: {
        show_signatures() {
            if (this.current_signature_group.short_name === 'SkyBlock') {
                if (this.skyblock.profile === null) {
                    return false;
                }
            }

            return true;
        },
    },
    mounted() {
        this.signatures = window.Paniek.signatures;
        this.urls       = window.Paniek.urls;

        let [currentSignatureGroup]  = Object.values(this.signatures);
        this.current_signature_group = currentSignatureGroup;

        this.loading = false;

        if (window.location.hash.trim() !== '') {
            this.username = window.location.hash.substr(1);
            this.getUuidFromUsername();
        }
    }
});
