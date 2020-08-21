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
    <div class="table-container">
        <sortable-table :data="members" class="guild-members compact bordered">
            <template v-slot:head>
                <tr>
                    <sortable-header :sticky="false" name="formatted_name" rowspan="2">
                        Username
                    </sortable-header>
                    <th colspan="5">
                        Total
                    </th>
                    <th colspan="2">
                        Normal
                    </th>
                    <th colspan="2">
                        Final
                    </th>
                </tr>
                <tr>
                    <!--Total-->
                    <sortable-header name="level">Stars</sortable-header>
                    <sortable-header name="wins">Wins</sortable-header>
                    <sortable-header name="kills">Kills</sortable-header>
                    <sortable-header name="kd">K/D</sortable-header>
                    <sortable-header name="wins_percentage">Games won</sortable-header>
                    <sortable-header name="beds_broken">Beds broken</sortable-header>
                    <!--Normal-->
                    <sortable-header name="kills_normal">Kills</sortable-header>
                    <sortable-header name="kd_normal">K/D</sortable-header>
                    <!--Final-->
                    <sortable-header name="kills_final">Kills</sortable-header>
                    <sortable-header name="kd_final">K/D</sortable-header>
                </tr>
            </template>
            <template v-slot="data">
                <td>
                    <img alt="" v-lazy="data.item.skin_url">
                    <div class="loader" v-if="data.item.loading">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span class="formatted-name" v-html="data.item.formatted_name"></span>
                </td>
                <!--Total-->
                <td>
                    {{ data.item.level|number_format }}
                </td>
                <td>
                    {{ data.item.wins|number_format }}
                </td>
                <td>
                    {{ data.item.kills|number_format }}
                </td>
                <td>
                    {{ data.item.kd|number_format }}
                </td>
                <td>
                    {{ data.item.wins_percentage|number_format }}%
                </td>
                <td>
                    {{ data.item.beds_broken|number_format }}
                </td>
                <!--Normal-->
                <td>
                    {{ data.item.kills_normal|number_format }}
                </td>
                <td>
                    {{ data.item.kd_normal|number_format }}
                </td>
                <!--Final-->
                <td>
                    {{ data.item.kills_final|number_format }}
                </td>
                <td>
                    {{ data.item.kd_final|number_format }}
                </td>
            </template>
            <template v-slot:footer>
                <tr>
                    <th>Guild Average</th>
                    <!--Total-->
                    <calculated-cell name="level"></calculated-cell>
                    <calculated-cell name="wins"></calculated-cell>
                    <calculated-cell name="kills"></calculated-cell>
                    <calculated-cell :precision="2" name="kd"></calculated-cell>
                    <calculated-cell :precision="1" name="wins_percentage">%</calculated-cell>
                    <calculated-cell name="beds_broken"></calculated-cell>
                    <!--Normal-->
                    <calculated-cell name="kills_normal"></calculated-cell>
                    <calculated-cell :precision="2" name="kd_normal"></calculated-cell>
                    <!--Final-->
                    <calculated-cell name="kills_final"></calculated-cell>
                    <calculated-cell :precision="2" name="kd_final"></calculated-cell>
                </tr>
                <tr>
                    <th>Guild Total</th>
                    <!--Total-->
                    <calculated-cell name="level" type="total"></calculated-cell>
                    <calculated-cell name="wins" type="total"></calculated-cell>
                    <calculated-cell name="kills" type="total"></calculated-cell>
                    <calculated-cell :precision="2" deaths="deaths" kills="kills" type="total_kd"></calculated-cell>
                    <calculated-cell :precision="1" total-played="games_played" type="total_wins_percentage" wins="wins">%</calculated-cell>
                    <calculated-cell name="beds_broken" type="total"></calculated-cell>
                    <!--Normal-->
                    <calculated-cell name="kills_normal" type="total"></calculated-cell>
                    <calculated-cell :precision="2" deaths="deaths_normal" kills="kills_normal" type="total_kd"></calculated-cell>
                    <!--Final-->
                    <calculated-cell name="kills_final" type="total"></calculated-cell>
                    <calculated-cell :precision="2" deaths="deaths_final" kills="kills_final" type="total_kd"></calculated-cell>
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
    name:       "BedWarsTable",
    components: {SortableTable, SortableHeader, CalculatedCell},
    props:      ['members']
}
</script>
