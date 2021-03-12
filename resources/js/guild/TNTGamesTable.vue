<!--
  - Copyright (c) 2021 Max Korlaar
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
                    <th colspan="2">
                        Bow Spleef
                    </th>
                    <th colspan="2">
                        TNT Run
                    </th>
                    <th colspan="2">
                        PVP Run
                    </th>
                    <th colspan="2">
                        TNT Tag
                    </th>
                    <th colspan="4">
                        Wizards
                    </th>
                </tr>
                <tr>
                    <!--Bow Spleef-->
                    <sortable-header name="wins_bowspleef">Wins</sortable-header>
                    <sortable-header name="shots_bowspleef">Shots</sortable-header>
                    <!--TNT Run-->
                    <sortable-header name="wins_tntrun">Wins</sortable-header>
                    <sortable-header name="record_tntrun_raw">Record</sortable-header>
                    <!--PVP Run-->
                    <sortable-header name="wins_pvprun">Wins</sortable-header>
                    <sortable-header name="record_pvprun_raw">Record</sortable-header>
                    <!--TNT Tag-->
                    <sortable-header name="wins_tnttag">Wins</sortable-header>
                    <sortable-header name="kills_tnttag">Kills</sortable-header>
                    <!--Wizards-->
                    <sortable-header name="wins_wizards">Wins</sortable-header>
                    <sortable-header name="kills_wizards">Kills</sortable-header>
                    <sortable-header name="assists_wizards">Assists</sortable-header>
                    <sortable-header name="kd_wizards">K/D</sortable-header>
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
                <!--Bow Spleef-->
                <td>
                    {{ data.item.wins_bowspleef|number_format }}
                </td>
                <td>
                    {{ data.item.shots_bowspleef|number_format }}
                </td>
                <!--TNT Run-->
                <td>
                    {{ data.item.wins_tntrun|number_format }}
                </td>
                <td>
                    {{ data.item.record_tntrun }}
                </td>
                <!--PVP Run-->
                <td>
                    {{ data.item.wins_pvprun|number_format }}
                </td>
                <td>
                    {{ data.item.record_pvprun }}
                </td>
                <!--TNT Tag-->
                <td>
                    {{ data.item.wins_tnttag|number_format }}
                </td>
                <td>
                    {{ data.item.kills_tnttag|number_format }}
                </td>
                <!--Wizards-->
                <td>
                    {{ data.item.wins_wizards|number_format }}
                </td>
                <td>
                    {{ data.item.kills_wizards|number_format }}
                </td>
                <td>
                    {{ data.item.assists_wizards|number_format }}
                </td>
                <td>
                    {{ data.item.kd_wizards|number_format }}
                </td>
            </template>
            <template v-slot:footer>
                <tr>
                    <th>Guild Average</th>
                    <!--Bow Spleef-->
                    <calculated-cell name="wins_bowspleef"></calculated-cell>
                    <calculated-cell name="shots_bowspleef"></calculated-cell>
                    <!--TNT Run-->
                    <calculated-cell name="wins_tntrun"></calculated-cell>
                    <calculated-cell :formatter="formatTime" name="record_tntrun_raw"></calculated-cell>
                    <!--PVP Run-->
                    <calculated-cell name="wins_pvprun"></calculated-cell>
                    <calculated-cell :formatter="formatTime" name="record_pvprun_raw"></calculated-cell>
                    <!--TNT Tag-->
                    <calculated-cell name="wins_tnttag"></calculated-cell>
                    <calculated-cell name="kills_tnttag"></calculated-cell>
                    <!--Wizards-->
                    <calculated-cell name="wins_wizards"></calculated-cell>
                    <calculated-cell name="kills_wizards"></calculated-cell>
                    <calculated-cell name="assists_wizards"></calculated-cell>
                    <calculated-cell :precision="2" name="kd_wizards"></calculated-cell>
                </tr>
                <tr>
                    <th>Guild Total</th>
                    <!--Bow Spleef-->
                    <calculated-cell name="wins_bowspleef" type="total"></calculated-cell>
                    <calculated-cell name="shots_bowspleef" type="total"></calculated-cell>
                    <!--TNT Run-->
                    <calculated-cell name="wins_tntrun" type="total"></calculated-cell>
                    <calculated-cell :formatter="formatTime" name="record_tntrun_raw" type="total"></calculated-cell>
                    <!--PVP Run-->
                    <calculated-cell name="wins_pvprun" type="total"></calculated-cell>
                    <calculated-cell :formatter="formatTime" name="record_pvprun_raw" type="total"></calculated-cell>
                    <!--TNT Tag-->
                    <calculated-cell name="wins_tnttag" type="total"></calculated-cell>
                    <calculated-cell name="kills_tnttag" type="total"></calculated-cell>
                    <!--Wizards-->
                    <calculated-cell name="wins_wizards" type="total"></calculated-cell>
                    <calculated-cell name="kills_wizards" type="total"></calculated-cell>
                    <calculated-cell name="assists_wizards" type="total"></calculated-cell>
                    <calculated-cell :precision="2" deaths="deaths_wizards" kills="kills_wizards" type="total_kd"></calculated-cell>
                </tr>
            </template>
        </sortable-table>
    </div>
</template>

<script>
import SortableTable from "../components/SortableTable";
import SortableHeader from "../components/SortableHeader";
import CalculatedCell from "../components/CalculatedCell";
import formatTime from "./_filters/timeFormat";

export default {
    name:       "TNTGamesTable",
    components: {SortableTable, SortableHeader, CalculatedCell},
    props:      ['members'],
    methods:    {
        formatTime
    }
}
</script>
