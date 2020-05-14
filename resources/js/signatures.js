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

const axios = require('axios').default;


// noinspection ObjectAllocationIgnored
window.signaturesApp = new Vue({
    el:      '#signature-app',
    data:    {
        signatures:         {},
        urls:               {
            get_uuid:    '',
            get_profile: '',
        },
        selected_signature: null,
        username:           null,
        uuid:               null,
        loading:            true,
        errors:             {},
    },
    methods: {
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
                }).finally(() => {
                    this.loading = false;
                })
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

        getImageUrl(signature) {
            return signature.url.replace(':username', this.uuid ? this.uuid : 'b876ec32e396476ba1158438d83c67d4');
        },

        getPreviewImageUrl(signature) {
            return this.getImageUrl(signature);
        },

        clearError(key) {
            this.errors[key] = null;
        }
    },
    watch:   {
        selected_signature(signature) {
            gtag('event', 'generate', {
                'event_category': 'signature',
                'event_label':    signature.name
            });
        }
    },
    mounted() {
        this.signatures = window.Paniek.signatures;
        this.urls       = window.Paniek.urls;
        this.loading    = false;

        if (window.location.hash.trim() !== '') {
            this.username = window.location.hash.substr(1);
            this.getUuidFromUsername();
        }
    }
});
