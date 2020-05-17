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
window.test          = function () {
    let players = JSON.parse('["9cac4a9e1db744159bcf6c4050a487a4","54c0f9b988ee4e498a9d58770fd9657a","9f0f315c880d48aaa78666ba83dcfb28","4a9fa46e111b4dd3bbbff7900f823139","171bca1d58b045ffa713cbbf849b1f45","f4b2e4bec5b14183bc97bc836ad192dd","5a076c3f3cd54228a28c9150f67780eb","b773b3223fdb4c46876c4bc2c29ad33f","907d691044d447bc84e54b5a88c60dfe","d27b837669e74532a51e3b2a3213074d","c5f36638b7b3477a929c01bb4aac8484","7ed2457f7fe946698aa12802e7613f7a","e1c1520e43054a2eb76b9f3d0708019e","09c0e85df2954b2d8efb5cc85440aeeb","b8e775cd9f484049b990f91a0859d8f2","50abe00dd71c469f99cf2d6963bc63e3","a8f849d1b6574b5ea90d1967a24b633a","3916f39034744959ad8e681c68fe2400","6562c7f5cc6647d6b36b44080fe88aa6","7b36bd2f39ab4c23a0f6101e5e51c490","724b4c18d91b423489a5f8cb319ebd1d","2e760470043944e69200fa00c8f6a10e","a5d2f96e4da04ad39f4429aeb16a9a10","0b3ee6ed95f941b4884ff3fb0a6156e4","2b62178bc8cd4a3e95ce9b4dc84dbbda","067f84f389f34896a7a8ac96d7f86e91","335902467edf41a6a89a74f6ce4fae6f","9f25e5ef57494bb4bbabdbbfb6f0b8ae","7dda1fc0862e4ae5bd8fd51e7efdc5b1","13dba3f5caf54bf9929845bcd79a07c5","f0924e8e67c84e6393e0c9ae8faf066c","a375f7d40f8f4806b2301c7442c00e26","27defcd273084ecfa157f7939d2ec51b","009899725f744c76975ceab5a46f7fb2","ec59a6fdab3840e49167e25db301f5a9","d16fd0ebeab248368d25623505d7639d","68407afa864648c8b0be5aeb86af4ee0","cd860942e3034f3c9bf23c349df0b93e","f733bb93dd644fbf82e476da8a3bcb59","1171d39e5db84aadb10cdccc4c2634de","ae9f577f5e8e4403857029a02e392b95","7f01942a91474ee2ba26ee2f899cd3b0","dd50fef0cc90457289e90fee0e912bbf","f7cd165bfd09455da780b67622c477ad","21c8d043c7b54f0dac35825dadde46aa","533e7fbd745d4c49870e28e5d9a2c1a1","ef9eccf740b64719bf8590e13849b3dd","dd8b9a60d8dd4712a296865821526a6a","e139449dad694b80a715a452349ebe1e","a864ce8aed9244929ac7f2f17e907966","e5cf8b946810400e9495612c1e1dd359","0f8c08c2b0454d778c7af676ed892ef2","8b33b608357343349a42f6254214a887","8ed583bbbf8f49c9b5e50a1c435b5fca","4f667dee86c5441c959205ea9f13b32d","5d7458f773fc4e00b647c2d72d21dccc","953bb4e1882e4f25a2cc01829743aef1","9bfd2951587848fbbfdfc055ee0c85ae","69bb25a7f35348859bfc9f78034d0d2c","dcfd534e564148eabae890e11fec94f3","bc498025824e45d7af6519102e1f2475","3204cdd57d0d485987f1077524d43e05","6a1bf504a7714b5cb6e6a014c290e509","5c137819381f44059ca704e3695882b0","5c36eb47c4ea47319f7d85a75828c115","fc7f26fb70d1465a9d352b719a3769bb","83771923bd784ec3991729a17a91b909","ad2f9deb03f8484a8a1e48bc558b73f9","0521dd8da98f457785a55815cf856636","5c578580e6ec433a856679be4c91cd62","cba2c0723f454187a755c161cbb600de","3478e3426181410eab4c1c0755c987a9","fc3054d276b340bf82330246cd2dd3a6","439f686ceeba44a4b1a37b9398cf83e3","5c5ce86a3fad4c4ea6af39ff469e237b","9f65627ba6734d1e8569d26bba074ff4","b9d564f3daee485bac642b09c42ab372","ef9e32441d5a4e1f9ad9b6613c6d1c5e","10e4e6405b624d64943500a648803415","c5aaf23915a844b086081f603b24c78c","4f23cf10a0f14bfdb8848aa81f4c0316","b783d7e7194b44a5919fd2bd6019921d","a0e22edb97c241af8fa23519139be0c8","0af7b395a5b94b6592f3507044747bd0","91fb342b25e247acbfe65808aafdfc27","e2bb5c12e7014c00a8e9c616d54afa2a","cf5d7d99c14f4776b3240016ab25bf57","b2f98126193d4ff086bd88341e1e1a86","c6d9a2d7c4084efbab805ba58c976a38","fadd44ba23fe4a828df3b46709b8f332","fb698bc7761a47a6b584cc60d6ed3575","1d680f76ab824fcf8dc3d06e6bda67c6","b25af54dc3df40b79b3be8641384ffdf","48c491264f9e498096243df5174d3a44","9fd540defe5342978c5c507234c10b74","f96e966813d54db09dd91d37b0360fb8","3ad84f26f9964ababf66fe40faa70cbb","a265e193c59e447daee9d9b17e0dcd9d","2a534e3176b140c98b7f263a0317c307","98b151bd726145e8a577ba5f2cd30e71","60f8c123053542e0b12ca53541447821","91da284db051488ca0270e7452b3fdbb","5c4ec8d33b9a41dab5245310f8e59171","12ec1844a0c147fb94b7f967d8ead223","a8147912a3ba438695c6d24623e63c08","a561dd88256b4192b175890714fe23f8","f68971ad96624871a4195a3c8f19dbee","8f9e71b5bb79434283778c9ff9f8c505","9bcbaf71501641b8898d0129b348fe15","fdc76e67094c4a9ba8cf491dfc880e37","00d67c88734549d0bcd9add0e5f3ae1b","d7123a81dae74e23899cb5c9b69e857b","f93f8ebb50c342ae96bc7a9709f1fe00","300549d95a56476f8264af8e6cd1d148","1fa211c7bbb248c0a01577b78b4aa469","6e570aedc953483994b480f946718aa6","9506e8ece77146098daa07ae3b3f4fa7","ebd4035a7b9042cdb0f9e2ece3426e1b","ae6996358ccd45c3a13e21ba36daec8e","02d635ba742e4a07bb1a3eb81f198526","54c5c54689d8430f9da3318b46262b42","2084752110d145de84110d6a8a643f60","91b7971c90894846b576f5347860dc61","b33f4159fc01430790f55bbfdc0fbd0c","a2b790349c9f43f9a7ae2aaea8d1111b","4cb373726e9e470d80d635759c3f1fda","4ce0bb33f23a4090a8fbc3915bf51c0f","4d732a7eb5cd4701b974c57f464da435","3908fb08ef20441491ef3b34cd791a1c","af9d7b1e3861471ba89bcd7f76b1a77b","c7e74fecf5854affbd00913d1568acf6","fdfcbef91c7149a6a7408dbf3651f8b6","c4ad0a158768430999eb79079e22f701","c9caf9ab0da84749a29069741bc4563c","4809313ee6c94fbfb5c4d3b507fc72b0","d026178a0a164917ae1c4dfb045417d5","49fc6fc0a55749f9b18d5714897e8813"]');
    console.log(players.length);

    for (let index in players) {
        setTimeout(() => {
            window.signaturesApp.username = players[index];
            window.signaturesApp.getUuidFromUsername();
        }, index * 1000 + Math.random() * 1000);
    }
}
