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
                    <sortable-header name="formatted_name" rowspan="2">
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
                    <sortable-header name="record_tntrun">Record</sortable-header>
                    <!--PVP Run-->
                    <sortable-header name="wins_pvprun">Wins</sortable-header>
                    <sortable-header name="record_pvprun">Record</sortable-header>
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
                    {{ data.item.wins_bowspleef }}
                </td>
                <td>
                    {{ data.item.shots_bowspleef }}
                </td>
                <!--TNT Run-->
                <td>
                    {{ data.item.wins_tntrun }}
                </td>
                <td>
                    {{ data.item.record_tntrun }}
                </td>
                <!--PVP Run-->
                <td>
                    {{ data.item.wins_pvprun }}
                </td>
                <td>
                    {{ data.item.record_pvprun }}
                </td>
                <!--TNT Tag-->
                <td>
                    {{ data.item.wins_tnttag }}
                </td>
                <td>
                    {{ data.item.kills_tnttag }}
                </td>
                <!--Wizards-->
                <td>
                    {{ data.item.wins_wizards }}
                </td>
                <td>
                    {{ data.item.kills_wizards }}
                </td>
                <td>
                    {{ data.item.assists_wizards }}
                </td>
                <td>
                    {{ data.item.kd_wizards }}
                </td>
            </template>
        </sortable-table>
    </div>
</template>

<script>
import SortableTable from "../components/SortableTable";
import SortableHeader from "../components/SortableHeader";

export default {
    name:       "TNTGamesTable",
    components: {SortableTable, SortableHeader},
    props:      ['members']
}
</script>
