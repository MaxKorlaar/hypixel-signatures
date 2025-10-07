<!--
  - Copyright (c) 2020-2024 Max Korlaar
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
    <div class="table-container">
        <sortable-table :data="members" class="guild-members compact bordered">
            <template v-slot:head>
                <tr>
                    <sortable-header :sticky="false" name="formatted_name">
                        Username
                    </sortable-header>
                    <sortable-header name="level">Level</sortable-header>
                    <sortable-header name="achievement_points">Achievement Points</sortable-header>
                    <sortable-header name="karma">Karma</sortable-header>
                    <sortable-header name="quests_completed">Quests Completed</sortable-header>
                    <sortable-header name="challenges_completed">Challenges Completed</sortable-header>
                </tr>
            </template>
            <template v-slot="data">
                <td>
                    <img :src="data.item.skin_url" alt="" loading="lazy">
                    <div v-if="data.item.loading" class="loader">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span class="formatted-name" v-html="data.item.formatted_name"></span>
                </td>
                <td>
                    {{ number_format(data.item.level) }}
                </td>
                <td>
                    {{ number_format(data.item.achievement_points) }}
                </td>
                <td>
                    {{ number_format(data.item.karma) }}
                </td>
                <td>
                    {{ number_format(data.item.quests_completed) }}
                </td>
                <td>
                    {{ number_format(data.item.challenges_completed) }}
                </td>
            </template>
            <template v-slot:footer>
                <tr>
                    <th>Guild Average</th>
                    <calculated-cell name="level"></calculated-cell>
                    <calculated-cell name="achievement_points"></calculated-cell>
                    <calculated-cell name="karma"></calculated-cell>
                    <calculated-cell name="quests_completed"></calculated-cell>
                    <calculated-cell name="challenges_completed"></calculated-cell>
                </tr>
                <tr>
                    <th>Guild Total</th>
                    <calculated-cell name="level" type="total"></calculated-cell>
                    <calculated-cell name="achievement_points" type="total"></calculated-cell>
                    <calculated-cell name="karma" type="total"></calculated-cell>
                    <calculated-cell name="quests_completed" type="total"></calculated-cell>
                    <calculated-cell name="challenges_completed" type="total"></calculated-cell>
                </tr>
            </template>
        </sortable-table>
    </div>
</template>

<script>
import SortableTable from "../components/SortableTable";
import SortableHeader from "../components/SortableHeader";
import CalculatedCell from "../components/CalculatedCell";

export default {
    name:       "GeneralStatisticsTable",
    components: {SortableTable, SortableHeader, CalculatedCell},
    props:      ['members'],
    methods:    {
        number_format(value) {
            if (isNaN(value)) return value;
            return (new Intl.NumberFormat()).format(value);
        }
    }
}
</script>
