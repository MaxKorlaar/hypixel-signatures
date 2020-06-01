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
                    {{ data.item.wins }}
                </td>
                <td>
                    {{ data.item.kills }}
                </td>
                <td>
                    {{ data.item.kd }}
                </td>
                <td>
                    {{ data.item.wins_percentage }}%
                </td>
                <!--Solo-->
                <td>
                    {{ data.item.wins_solo }}
                </td>
                <td>
                    {{ data.item.kills_solo }}
                </td>
                <td>
                    {{ data.item.kd_solo }}
                </td>
                <td>
                    {{ data.item.wins_percentage_solo }}%
                </td>
                <!--Teams-->
                <td>
                    {{ data.item.wins_teams }}
                </td>
                <td>
                    {{ data.item.kills_teams }}
                </td>
                <td>
                    {{ data.item.kd_teams }}
                </td>
                <td>
                    {{ data.item.wins_percentage_teams }}%
                </td>
                <!--Mega-->
                <td>
                    {{ data.item.wins_mega }}
                </td>
                <td>
                    {{ data.item.kills_mega }}
                </td>
                <td>
                    {{ data.item.kd_mega }}
                </td>
                <td>
                    {{ data.item.wins_percentage_mega }}%
                </td>
            </template>
        </sortable-table>
    </div>
</template>

<script>
import SortableTable from "../components/SortableTable";
import SortableHeader from "../components/SortableHeader";

export default {
    name:       "SkyWarsTable",
    components: {SortableTable, SortableHeader},
    props:      ['members']
}
</script>
