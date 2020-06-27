<!--
  - Copyright (c) 2020 Max Korlaar
  - All rights reserved.
  -
  - Redistribution and use in source and binary forms, with or without
  - modification, are permitted provided that the following conditions are met:
  -
  - * Redistributions of source code must retain the above copyright notice, this
  -   list of conditions and the following disclaimer.
  -
  - * Redistributions in binary form must reproduce the above copyright notice,
  -   this list of conditions, a visible attribution to the original author(s)
  -   of the software available to the public, and the following disclaimer
  -   in the documentation and/or other materials provided with the distribution.
  -
  - * Neither the name of the copyright holder nor the names of its
  -   contributors may be used to endorse or promote products derived from
  -   this software without specific prior written permission.
  -
  - THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
  - AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
  - IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
  - DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
  - FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
  - DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
  - SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
  - CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
  - OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
  - OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
  -->

<template>
    <td>
        {{ value }}
        <slot></slot>
    </td>
</template>

<script>
import {meanBy, round, sumBy} from "lodash/math";

export default {
    name:     "CalculatedCell",
    props:    {
        name:        String,
        kills:       String,
        deaths:      String,
        wins:        String,
        losses:      String,
        totalPlayed: String,
        precision:   {
            type:    Number,
            default: 0
        },
        type:        {
            type:    String,
            default: 'average'
        },
        formatter:   Function
    },
    data() {
        return {
            formatterMethod() {
                return typeof this.formatter === 'undefined' ? (value => round(value, this.precision)) : this.formatter;
            }
        }
    },
    computed: {
        value() {
            let number;

            if (this.type === 'total') {
                number = sumBy(this.$parent.data, item => {
                    return isNaN(item[this.name]) ? 0 : item[this.name];
                });
            } else if (this.type === 'total_kd') {
                let kills = sumBy(this.$parent.data, item => {
                    return isNaN(item[this.kills]) ? 0 : item[this.kills];
                });

                let deaths = sumBy(this.$parent.data, item => {
                    return isNaN(item[this.deaths]) ? 0 : item[this.deaths];
                });

                if (deaths > 0) {
                    number = kills / deaths;
                } else {
                    return 'N/A';
                }
            } else if (this.type === 'total_wins_percentage') {
                let wins = sumBy(this.$parent.data, item => {
                    return isNaN(item[this.wins]) ? 0 : item[this.wins];
                });

                let total;

                if (this.totalPlayed) {
                    total = sumBy(this.$parent.data, item => {
                        return isNaN(item[this.totalPlayed]) ? 0 : item[this.totalPlayed];
                    });
                } else {
                    let losses = sumBy(this.$parent.data, item => {
                        return isNaN(item[this.losses]) ? 0 : item[this.losses];
                    });

                    total = wins + losses;
                }

                if (total > 0) {
                    number = (wins / total) * 100;
                } else {
                    number = 0;
                }
            } else {
                number = meanBy(this.$parent.data, item => {
                    return isNaN(item[this.name]) ? 0 : item[this.name];
                });
            }

            return this.formatterMethod()(number);
        }
    }
}
</script>
