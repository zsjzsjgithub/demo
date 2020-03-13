<template>
  <div>
    <el-form :inline="true" size="mini" class="search" label-width="80px">
      <el-form-item :label="$t('menu_agent')" v-if="isAdmin">
        <el-select v-model="search.agent_id" filterable clearable>
          <el-option
              v-for="a in agents"
              :key="a.id"
              :label="`${a.nickname} (${a.username})`"
              :value="a.id">
          </el-option>
        </el-select>
      </el-form-item>
      <el-form-item :label="$t('date')">
        <el-date-picker
            v-model="search.date"
            type="daterange"
            :start-placeholder="$t('time_start')"
            :end-placeholder="$t('time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="getData" v-t="'search'"></el-button>
      </el-form-item>
    </el-form>
    <el-table
        :data="list"
        ref="table"
        border
        size="mini"
        :span-method="spanMethod"
        :row-class-name="rowClass"
        row-key="id">
      <el-table-column :label="$t('date')" width="90" align="center">
        <template slot-scope="{row}">
          <b v-if="row.type !== 'data'">{{row.date}}</b>
          <span v-else>{{row.date}}</span>
        </template>
      </el-table-column>
      <el-table-column :label="$t('menu_agent')" width="160" align="center">
        <agent slot-scope="{row}" :data="row.agent"></agent>
      </el-table-column>
      <el-table-column :label="$t('commission_rate')" width="70" align="center">
        <span slot-scope="{row}">{{row.agent ? row.agent.commission_rate + '%' : ''}}</span>
      </el-table-column>
      <el-table-column :label="$t('deposit_sum')" align="center">
        <span slot-scope="{row}">{{row.deposit|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('withdrawal_sum')" align="center">
        <span slot-scope="{row}">{{row.withdrawal|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('lr')" align="center">
        <template slot-scope="{row}">
          <span style="color: #67C23A;" v-if="Number(row.profit) >= 0">
            {{row.profit|numFormat}}
          </span>
          <span style="color: #ff4949;" v-else>{{row.profit|numFormat}}</span>
        </template>
      </el-table-column>
      <el-table-column :label="$t('sr')" align="center">
        <template slot-scope="{row}">
          <span style="color: #67C23A;" v-if="Number(row.income) >= 0">
            {{row.income|numFormat}}
          </span>
          <span style="color: #ff4949;" v-else>{{row.income|numFormat}}</span>
        </template>
      </el-table-column>
      <el-table-column prop="login" :label="$t('login_count')" align="center"></el-table-column>
      <el-table-column prop="deposit_count" :label="$t('deposit_count')" align="center"></el-table-column>
      <el-table-column prop="withdrawal_count" :label="$t('withdrawal_count')" align="center"></el-table-column>
    </el-table>
  </div>
</template>

<script>
  import {agents} from '../datas';
  import {mapState} from 'vuex'

  export default {
    name: 'statistic',
    data() {
      return {
        list: [],
        agents: [],
        search: {
          agent_id: '',
          date: [
            moment().utcOffset(TZ).subtract(10, 'd').startOf('day').format(),
            moment().utcOffset(TZ).endOf('day').format()
          ]
        }
      }
    },
    computed: {
      ...mapState(['isAdmin']),
      params() {
        let params = Object.assign({}, this.search)

        for (let p in params) {
          if (!params[p]) {
            delete params[p]
          }
        }

        if (params.date) {
          if (params.date.length === 2) {
            if (typeof params.date[0] === 'string') {
              params.date_start = params.date[0]
              params.date_end = params.date[1]
            } else {
              params.date_start = params.date[0].format()
              params.date_end = params.date[1].format()
            }
          }
          delete params.date
        }

        return params
      }
    },
    methods: {
      getData() {
        this.$api.get('/datas/statistics', {params: this.params}).then(data => {
          if (data) {
            this.list = data
          }
        })
      },
      spanMethod({row, column, columnIndex}) {
        if (row.type !== 'data') {
          switch (columnIndex) {
            case 0:
              return [1, 3]

            case 1:
              return [0, 0]

            case 2:
              return [0, 0]
          }
        }
      },
      rowClass({row}) {
        return row.type
      }
    },
    mounted() {
      this.getData()
      agents().then(data => {
        if (data) {
          this.agents = data
        }
      })
    }
  }
</script>

<style lang="less">
  .el-table .total {
    background: #f7f7f7;
    font-weight: bold;
  }

  .el-table .sum {
    background: #f7f7f7;
  }
</style>