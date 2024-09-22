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

    return [
        'title' => 'Hypixel Dynamische Afbeeldingen',

        'groups' => [
            'generic'  => [
                'name'        => 'Algemene afbeeldingen',
                'short_name'  => 'Algemeen',
                'description' => 'Algemene statistieken voor de meest populaire Hypixel-minigames of je Hypixel-profiel.',
            ],
            'skywars'  => [
                'name'        => 'SkyWars-afbeeldingen',
                'short_name'  => 'SkyWars',
                'description' => 'Hypixel SkyWars-statistieken, zoals het aantal overwinningen, je SkyWars-level en je kill/death ratio.'
            ],
            'guild'    => [
                'name'        => 'Gilde-afbeeldingen',
                'short_name'  => 'Gilde',
                'description' => 'Algemene statistieken over de gilde waarvan je lid bent op Hypixel. De generieke gilde-statistieken-afbeelding laat zelfs de vlag van de gilde zien als ze er een heeft!',
            ],
            'skyblock' => [
                'name'        => 'SkyBlock-afbeeldingen',
                'short_name'  => 'SkyBlock',
                'description' => 'Hypixel SkyBlock-statistieken, met de hand gemaakt om alle SkyBlock-gegevens op je account uit te lezen!',
            ]
        ],

        'username_or_uuid' => 'Minecraft-gebruikersnaam of UUID',
        'username_help'    => 'Voer hier je Minecraft-gebruikersnaam of UUID in zodat wij je statistieken via de Hypixel-API kunnen opzoeken. Als je niet weet wat je UUID is, voer dan gewoon je gebruikersnaam in en dan zoeken wij hem voor je op.',

        'skyblock' => [
            'no_profiles'      => 'Het lijkt erop dat je geen SkyBlock-profielen hebt op je account. Speel het eens als je dat nog niet gedaan hebt, of zet je API-statistieken aan als je die uitgezet hebt.',
            'select_a_profile' => 'Kies eerst een SkyBlock-profiel.',
            'profile'          => 'SkyBlock-profiel',
        ],

        'your_signature'      => 'Jouw afbeelding',
        'your_signature_text' => [
            'Hieronder vind je de afbeelding die jij hebt gekozen. Je kan ernaar verwijzen met de URL van de afbeelding, of je kan de BBcode kopiëren en plakken op de forums in een forumbericht of je handtekening.',
            'Het is belangrijk dat je de BBcode of de URL hieronder gebruikt om te verwijzen naar je afbeelding. Als je de afbeelding downloadt of de afbeelding zelf kopieert en plakt, dan zal deze niet automatisch worden bijgewerkt.'
        ],

        'bbcode'              => 'BBcode',
        'bbcode_instructions' => 'Kopieer en plak bovenstaand stuk tekst in <a target="_blank" href="https://hypixel.net/account/signature">je forum-handtekening</a> om automatisch je gekozen statistieken onder elk forumbericht dat je maakt te tonen. Je kan de URL van de afbeelding gebruiken om deze handtekening op andere websites te gebruiken als je dat wilt.',

        'direct_link' => 'Directe link',

        'signature_options'      => 'Afbeeldingsopties',
        'signature_options_text' => 'Deze afbeelding biedt opties die je kan aanpassen om het uiterlijk ervan aan te passen:',
    ];
