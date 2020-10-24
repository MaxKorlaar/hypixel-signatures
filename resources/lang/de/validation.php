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

    return [

        /*
        |--------------------------------------------------------------------------
        | Validation Language Lines
        |--------------------------------------------------------------------------
        |
        | The following language lines contain the default error messages used by
        | the validator class. Some of these rules have multiple versions such
        | as the size rules. Feel free to tweak each of these messages here.
        |
        */

        'accepted'             => 'Das :attribute muss akzeptiert werden.',
        'active_url'           => 'Das :attribute ist keine gültige URL. '
        'after'                => 'Das :attribute muss ein Datum nach :date sein.',
        'after_or_equal'       => 'Das :attribute muss ein Datum nach oder gleich sein :date.',
        'alpha'                => 'Das :attribute darf nur Buchstaben enthalten.',
        'alpha_dash'           => 'Das :attribute darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten.',
        'alpha_num'            => 'Das :attribute darf nur Buchstaben und Zahlen enthalten.',
        'array'                => 'Das :attribute muss ein Array sein.',
        'before'               => 'Das :attribute muss ein Datum vor :date sein.',
        'before_or_equal'      => 'Das :attribute muss ein Datum vor oder gleich sein :date.',
        'between'              => [
            'numeric' => 'Das :attribute muss zwischen sein :min und :max.',
            'file'    => 'Das :attribute muss zwischen sein :min und :max kilobytes.',
            'string'  => 'Das :attribute muss zwischen sein :min und :max characters.',
            'array'   => 'Das :attribute muss zwischen sein :min und :max items.',
        ],
        'boolean'              => 'Das :attribute Feld muss wahr oder falsch sein.',
        'confirmed'            => 'Das :attribute Bestätigung stimmt nicht überein.',
        'date'                 => 'Das :attribute ist kein gültiges Datum.',
        'date_equals'          => 'Das :attribute muss ein Datum sein, das gleich ist :date.',
        'date_format'          => 'Das :attribute stimmt nicht mit dem Format überein :format.',
        'different'            => 'Das :attribute und :other müssen anders sein.',
        'digits'               => 'Das :attribute muss sein :digits Ziffern.',
        'digits_between'       => 'Das :attribute muss zwischenc :min und :max Ziffern liegen.',
        'dimensions'           => 'Das :attribute hat ungültige Bildabmessungen.',
        'distinct'             => 'Das :attribute Feld hat einen doppelten Wert.',
        'email'                => 'Das :attribute muss eine gültige E-Mail-Adresse sein.',
        'ends_with'            => 'Das :attribute muss mit einem der folgenden Werte enden: :values',
        'exists'               => 'Das ausgewählt :attribute ist ungültig.',
        'file'                 => 'Das :attribute muss eine Datei sein.',
        'filled'               => 'Das :attribute Feld muss einen Wert haben.',
        'gt'                   => [
            'numeric' => 'Das :attribute muss größer als :value sein.',
            'file'    => 'Das :attribute muss größer als :value Kilobyte sein.',
            'string'  => 'Das :attribute muss größer als: value Charakter sein.',
            'array'   => 'Das :attribute muss mehr als :value Elemente haben.',
        ],
        'gte'                  => [
            'numeric' => 'Das :attribute muss größer oder gleich :value sein.',
            'file'    => 'Das :attribute muss größer oder gleich :value kilobytes sein.',
            'string'  => 'Das :attribute muss besser oder gleich :value Zeichen sein.',
            'array'   => 'Das :attribute muss :value Artikel oder mehr haben.',
        ],
        'image'                => 'Das :attribute muss ein Bild sein.',
        'in'                   => 'Das :attribute selected ist ungültig.',
        'in_array'             => 'Das :attribute Feld existiert nicht in :other.',
        'integer'              => 'Das :attribute muss eine ganze Zahl sein.',
        'ip'                   => 'Das :attributemuss eine gültige IP-Adresse sein.',
        'ipv4'                 => 'Das :attribute muss eine gültige IPv4-Adresse sein.',
        'ipv6'                 => 'Das :attribute muss eine gültige IPv6-Adresse sein.',
        'json'                 => 'Das :attribute muss eine gültige JSON-Zeichenfolge sein.',
        'lt'                   => [
            'numeric' => 'Das :attribute muss kleiner als :value sein.',
            'file'    => 'Das :attribute muss kleiner als :value kilobytes sein.',
            'string'  => 'Das :attribute muss kleiner als :value Zeichen sein.',
            'array'   => 'Das :attribute muss weniger als :value Artikel haben sein.',
        ],
        'lte'                  => [
            'numeric' => 'Das :attribute muss kleiner oder gleich :value sein.',
            'file'    => 'Das :attribute muss kleiner oder gleich :value kilobytes sein.',
            'string'  => 'Das :attribute muss kleiner oder gleich :value Zeichen sein.',
            'array'   => 'Das :attribute darf nicht mehr als :value Artikel enthalten.',
        ],
        'max'                  => [
            'numeric' => 'Das :attribute darf nicht größer als :max sein.',
            'file'    => 'Das :attribute darf nicht größer als :max kilobytes sein.',
            'string'  => 'Das :attribute darf nicht größer als :max Zeichen sein .',
            'array'   => 'Das :attribute darf nicht mehr als :max Artikel haben.',
        ],
        'mimes'                => 'Das :attribute muss eine Datei vom Typ sein: :values.',
        'mimetypes'            => 'Das :attribute muss eine Datei vom Typ sein: :values.',
        'min'                  => [
            'numeric' => 'Das :attribute muss mindestesn :min sein .',
            'file'    => 'Das :attribute muss mindestesn :min kilobytes sein .',
            'string'  => 'Das :attribute muss mindestesn :min Zeichen sein .',
            'array'   => 'Das :attribute muss mindestens :min Artikel haben.',
        ],
        'not_in'               => 'Das :attribute selected ist ungültig.',
        'not_regex'            => 'Das :attribute Format ist ungültig.',
        'numeric'              => 'Das :attribute must be a number.',
        'password'             => 'Das Passwort ist falsch.',
        'present'              => 'Das :attribute Feld muss vorhanden sein.',
        'regex'                => 'Das :attribute Format ist ungültig.',
        'required'             => 'Das :attribute Feld ist erforderlich.',
        'required_if'          => 'Das :attribute Feld ist erforderlich, wenn :other is :value.',
        'required_unless'      => 'Das :attribute Feld ist erforderlich, es sei denn :other ist nicht in :values.',
        'required_with'        => 'Das :attribute Feld ist erforderlich, wenn :values vorhanden ist.',
        'required_with_all'    => 'Das :attribute Feld ist erforderlich, wenn :values vorhanden sind.',
        'required_without'     => 'Das :attribute Feld ist erforderlich, wenn :values nicht vorhanden ist.',
        'required_without_all' => 'Das :attribute Feld ist erforderlich, wenn keine von :values vorhanden ist.',
        'same'                 => 'Das :attribute und :other müssen übereinstimmen.',
        'size'                 => [
            'numeric' => 'Das :attribute muss :size sein.',
            'file'    => 'Das :attribute muss :size kilobytes sein.',
            'string'  => 'Das :attribute muss :size Zeichen sein.',
            'array'   => 'Das :attribute muss :size Elemente enthalten.',
        ],
        'starts_with'          => 'Das :attribute muss mit einem der folgenden Punkte beginnen: :values',
        'string'               => 'Das :attribute muss eine Zeichenfolge sein.',
        'timezone'             => 'Das :attribute muss eine gültige Zone sein.',
        'unique'               => 'Das :attribute wurde bereits genommen.',
        'uploaded'             => 'Das :attribute Fehler beim Hochladen.',
        'url'                  => 'Das :attribute Format ist ungültig.',
        'uuid'                 => 'Das :attribute muss eine gültige UUID sein.',

        /*
        |--------------------------------------------------------------------------
        | Custom Validation Language Lines
        |--------------------------------------------------------------------------
        |
        | Here you may specify custom validation messages for attributes using the
        | convention "attribute.rule" to name the lines. This makes it quick to
        | specify a specific custom language line for a given attribute rule.
        |
        */

        'custom' => [
            'attribute-name' => [
                'rule-name' => 'benutzerdefinierte Nachricht',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Custom Validation Attributes
        |--------------------------------------------------------------------------
        |
        | The following language lines are used to swap our attribute placeholder
        | with something more reader friendly such as "E-Mail Address" instead
        | of "email". This simply helps us make our message more expressive.
        |
        */

        'attributes' => [],

    ];
