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
    <table>
        <thead>
            <slot name="head"></slot>
        </thead>
        <tbody>
            <tr :class="{ 'loading': item.loading }" v-for="item in sorted_data">
                <slot v-bind:item="item"></slot>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    name:     "SortableTable",
    props:    ['data'],
    data() {
        return {
            sort: {
                by:        null,
                direction: 'asc'
            },
        }
    },
    computed: {
        sorted_data() {
            if (this.sort.by !== null) {
                return [...this.data].sort((a, b) => {
                    const modifier = this.sort.direction === 'asc' ? 1 : -1;

                    if (a[this.sort.by] < b[this.sort.by]) return -1 * modifier;
                    if (a[this.sort.by] > b[this.sort.by]) return modifier;

                    return 0;
                });
            }

            return this.data;
        }
    },
    mounted() {
        this.$on('sortBy', field => {
            if (field === this.sort.by) {
                switch (this.sort.direction) {
                    case "asc":
                        this.sort.direction = 'desc';
                        break;
                    case 'desc':
                        this.sort.direction = 'asc';
                        this.sort.by        = null;
                        break;
                }
            } else {
                this.sort.direction = 'asc';
                this.sort.by        = field;
            }
        })
    }
}
</script>
