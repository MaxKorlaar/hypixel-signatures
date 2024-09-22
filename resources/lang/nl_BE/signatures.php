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
                'description' => 'Algemene statistieken voor de meest populaire Hypixel-minigames of uw Hypixel-profiel.',
            ],
            'skywars'  => [
                'name'        => 'SkyWars-afbeeldingen',
                'short_name'  => 'SkyWars',
                'description' => 'Hypixel SkyWars-statistieken, zoals het aantal overwinningen, uw SkyWars-level en uw kill/death ratio.'
            ],
            'guild'    => [
                'name'        => 'Gilde-afbeeldingen',
                'short_name'  => 'Gilde',
                'description' => 'Algemene statistieken over de guild waarvan u lid bent op Hypixel. De generieke guild-statistieken-afbeelding laat zelfs de vlag van de guild zien als ze er een heeft!',
            ],
            'skyblock' => [
                'name'        => 'SkyBlock-afbeeldingen',
                'short_name'  => 'SkyBlock',
                'description' => 'Hypixel SkyBlock-statistieken, met de hand gemaakt om alle SkyBlock-gegevens op uw account uit te lezen!',
            ]
        ],

        'username_or_uuid' => 'Minecraft-gebruikersnaam of UUID',
        'username_help'    => 'Voer hier uw Minecraft-gebruikersnaam of UUID in zodat wij uw statistieken via de Hypixel-API kunnen opzoeken. Als u niet weet wat uw UUID is, voer dan gewoon uw gebruikersnaam in en dan zoeken wij hem voor u op.',

        'skyblock' => [
            'no_profiles'      => 'Het lijkt erop dat u geen SkyBlock-profielen hebt op uw account. Speel het eens als u dat nog niet gedaan hebt, of zet uw API-statistieken aan als u die uitgezet hebt.',
            'select_a_profile' => 'Kies eerst een SkyBlock-profiel.',
            'profile'          => 'SkyBlock-profiel',
        ],

        'your_signature'      => 'Jouw afbeelding',
        'your_signature_text' => [
            'Hieronder vind u de afbeelding die jij hebt gekozen. U kunt ernaar verwijzen met de URL van de afbeelding, of u kunt de BBcode kopiëren en plakken op de forums in een forumbericht of uw handtekening.',
            'Het is belangrijk dat u de BBcode of de URL hieronder gebruikt om te verwijzen naar uw afbeelding. Als u de afbeelding downloadt of de afbeelding zelf kopieert en plakt, dan zal deze niet automatisch worden bijgewerkt.'
        ],

        'bbcode'              => 'BBcode',
        'bbcode_instructions' => 'Kopieer en plak bovenstaand stuk tekst in <a target="_blank" href="https://hypixel.net/account/signature">uw forum-handtekening</a> om automatisch uw gekozen statistieken onder elk forumbericht dat u maakt te tonen. U kan de URL van de afbeelding gebruiken om deze handtekening op andere websites te gebruiken als u dat wilt.',

        'direct_link' => 'Directe link',

        'signature_options'      => 'Afbeeldingsopties',
        'signature_options_text' => 'Deze afbeelding biedt opties die u kan aanpassen om het uiterlijk ervan aan te passen:',
    ];
