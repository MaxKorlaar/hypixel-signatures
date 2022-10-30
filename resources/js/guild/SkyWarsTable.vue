<!--
  - Copyright (c) 2020-2022 Max Korlaar
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
                    <th colspan="4">
                        Total
                    </th>
                    <th colspan="4">
                        Solo
                    </th>
                    <th colspan="4">
                        Teams
                    </th>
                    <th colspan="4">
                        Mega
                    </th>
                </tr>
                <tr>
                    <!--Total-->
                    <sortable-header name="wins">Wins</sortable-header>
                    <sortable-header name="kills">Kills</sortable-header>
                    <sortable-header name="kd">K/D</sortable-header>
                    <sortable-header name="wins_percentage">Games won</sortable-header>
                    <!--Solo-->
                    <sortable-header name="wins_solo">Wins</sortable-header>
                    <sortable-header name="kills_solo">Kills</sortable-header>
                    <sortable-header name="kd_solo">K/D</sortable-header>
                    <sortable-header name="wins_percentage_solo">Games won</sortable-header>
                    <!--Teams-->
                    <sortable-header name="wins_teams">Wins</sortable-header>
                    <sortable-header name="kills_teams">Kills</sortable-header>
                    <sortable-header name="kd_teams">K/D</sortable-header>
                    <sortable-header name="wins_percentage_teams">Games won</sortable-header>
                    <!--Mega-->
                    <sortable-header name="wins_mega">Wins</sortable-header>
                    <sortable-header name="kills_mega">Kills</sortable-header>
                    <sortable-header name="kd_mega">K/D</sortable-header>
                    <sortable-header name="wins_percentage_mega">Games won</sortable-header>
                </tr>
            </template>
            <template v-slot="data">
                <td>
                    <img :src="data.item.skin_url" alt="" height="42" loading="lazy" width="38">
                    <div class="loader" v-if="data.item.loading">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span class="formatted-name" v-html="data.item.formatted_name"></span>
                </td>
                <!--Total-->
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
                    {{ data.item.wins_percentage }}%
                </td>
                <!--Solo-->
                <td>
                    {{ data.item.wins_solo|number_format }}
                </td>
                <td>
                    {{ data.item.kills_solo|number_format }}
                </td>
                <td>
                    {{ data.item.kd_solo|number_format }}
                </td>
                <td>
                    {{ data.item.wins_percentage_solo }}%
                </td>
                <!--Teams-->
                <td>
                    {{ data.item.wins_teams|number_format }}
                </td>
                <td>
                    {{ data.item.kills_teams|number_format }}
                </td>
                <td>
                    {{ data.item.kd_teams|number_format }}
                </td>
                <td>
                    {{ data.item.wins_percentage_teams }}%
                </td>
                <!--Mega-->
                <td>
                    {{ data.item.wins_mega|number_format }}
                </td>
                <td>
                    {{ data.item.kills_mega|number_format }}
                </td>
                <td>
                    {{ data.item.kd_mega|number_format }}
                </td>
                <td>
                    {{ data.item.wins_percentage_mega }}%
                </td>
            </template>
            <template v-slot:footer>
                <tr>
                    <th>Guild Average</th>
                    <!--Total-->
                    <calculated-cell name="wins"></calculated-cell>
                    <calculated-cell name="kills"></calculated-cell>
                    <calculated-cell :precision="2" name="kd"></calculated-cell>
                    <calculated-cell :precision="1" name="wins_percentage">%</calculated-cell>
                    <!--Solo-->
                    <calculated-cell name="wins_solo"></calculated-cell>
                    <calculated-cell name="kills_solo"></calculated-cell>
                    <calculated-cell :precision="2" name="kd_solo"></calculated-cell>
                    <calculated-cell :precision="1" name="wins_percentage_solo">%</calculated-cell>
                    <!--Teams-->
                    <calculated-cell name="wins_teams"></calculated-cell>
                    <calculated-cell name="kills_teams"></calculated-cell>
                    <calculated-cell :precision="2" name="kd_teams"></calculated-cell>
                    <calculated-cell :precision="1" name="wins_percentage_teams">%</calculated-cell>
                    <!--Mega-->
                    <calculated-cell name="wins_mega"></calculated-cell>
                    <calculated-cell name="kills_mega"></calculated-cell>
                    <calculated-cell :precision="2" name="kd_mega"></calculated-cell>
                    <calculated-cell :precision="1" name="wins_percentage_mega">%</calculated-cell>
                </tr>
                <tr>
                    <th>Guild Total</th>
                    <!--Total-->
                    <calculated-cell name="wins" type="total"></calculated-cell>
                    <calculated-cell name="kills" type="total"></calculated-cell>
                    <calculated-cell :precision="2" deaths="deaths" kills="kills" type="total_kd"></calculated-cell>
                    <calculated-cell :precision="1" losses="losses" type="total_wins_percentage" wins="wins">%</calculated-cell>
                    <!--Solo-->
                    <calculated-cell name="wins_solo" type="total"></calculated-cell>
                    <calculated-cell name="kills_solo" type="total"></calculated-cell>
                    <calculated-cell :precision="2" deaths="deaths_solo" kills="kills_solo" type="total_kd"></calculated-cell>
                    <calculated-cell :precision="1" losses="losses_solo" type="total_wins_percentage" wins="wins_solo">%</calculated-cell>
                    <!--Teams-->
                    <calculated-cell name="wins_teams" type="total"></calculated-cell>
                    <calculated-cell name="kills_teams" type="total"></calculated-cell>
                    <calculated-cell :precision="2" deaths="deaths_teams" kills="kills_teams" type="total_kd"></calculated-cell>
                    <calculated-cell :precision="1" losses="losses_teams" type="total_wins_percentage" wins="wins_teams">%</calculated-cell>
                    <!--Mega-->
                    <calculated-cell name="wins_mega" type="total"></calculated-cell>
                    <calculated-cell name="kills_mega" type="total"></calculated-cell>
                    <calculated-cell :precision="2" deaths="deaths_mega" kills="kills_mega" type="total_kd"></calculated-cell>
                    <calculated-cell :precision="1" losses="losses_mega" type="total_wins_percentage" wins="wins_mega">%</calculated-cell>
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
    name:       "SkyWarsTable",
    components: {CalculatedCell, SortableTable, SortableHeader},
    props: ['members']
}
</script>
