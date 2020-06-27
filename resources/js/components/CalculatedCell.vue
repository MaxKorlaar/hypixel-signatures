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
import {meanBy, round, sumBy} from "lodash";

export default {
    name:     "CalculatedCell",
    props:    {
        name:      String,
        precision: {
            type:    Number,
            default: 0
        },
        type:      {
            type:    String,
            default: 'average'
        }
    },
    computed: {
        value() {
            let number;

            if (this.type === 'total') {
                number = sumBy(this.$parent.data, item => {
                    return isNaN(item[this.name]) ? 0 : item[this.name];
                });
            } else {
                number = meanBy(this.$parent.data, item => {
                    return isNaN(item[this.name]) ? 0 : item[this.name];
                });
            }

            return round(number, this.precision);
        }
    }
}
</script>
