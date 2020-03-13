<template>
  <div>
    <el-form :inline="true" size="mini" class="search" label-width="80px">
      <el-form-item :label="`${$t('username')}|${$t('nickname')}`">
        <el-input v-model="search.name" clearable></el-input>
      </el-form-item>
      <el-form-item :label="$t('time_sq')">
        <el-date-picker
            v-model="search.created_date"
            type="daterange"
            :start-placeholder="$t('time_start')"
            :end-placeholder="$t('time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item :label="$t('time_cl')">
        <el-date-picker
            v-model="search.date"
            type="daterange"
            :start-placeholder="$t('time_start')"
            :end-placeholder="$t('time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item :label="$t('type')">
        <el-select v-model="search.type" clearable>
          <el-option value="1" :label="$t('type_1')"></el-option>
          <el-option value="2" :label="$t('type_2')"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item :label="$t('status')">
        <el-select v-model="search.status" clearable>
          <el-option value="1" :label="$t('status_1')"></el-option>
          <el-option value="4" :label="$t('status_4')"></el-option>
          <el-option value="2" :label="$t('status_2')"></el-option>
          <el-option value="3" :label="$t('status_3')"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="getData(true)" v-t="'search'"></el-button>
      </el-form-item>
    </el-form>
    <el-table
        :data="list"
        ref="table"
        border
        size="mini"
        row-key="id"
        :row-class-name="rowClass">
      <el-table-column prop="id" label="#" min-width="55"></el-table-column>
      <el-table-column :label="$t('member')" min-width="140">
        <member slot-scope="{row}" :data="row.member"></member>
      </el-table-column>
      <el-table-column :label="$t('amount')" min-width="90">
        <span slot-scope="{row}">{{row.amount|numFormat}}</span>
      </el-table-column>
      <el-table-column prop="created_at" :label="$t('time_sq')" min-width="140"></el-table-column>
      <el-table-column :label="$t('time_cl')" min-width="140">
        <span slot-scope="{row}" v-if="row.status !== 1">{{row.updated_at}}</span>
      </el-table-column>
      <el-table-column :label="$t('type')">
        <template slot-scope="{row}">
          <span v-if="row.type === 2" style="color: #F56C6C;">{{row.type_label}}</span>
          <span v-else>{{row.type_label}}</span>
        </template>
      </el-table-column>
      <el-table-column :label="$t('status')">
        <template slot-scope="{row}">
          <el-tag size="mini" v-if="row.status === 1">{{row.status_label}}</el-tag>
          <el-tag size="mini" type="danger" v-if="row.status === 2">{{row.status_label}}</el-tag>
          <el-tag size="mini" type="success" v-if="row.status === 3">{{row.status_label}}</el-tag>
          <el-tag size="mini" type="info" v-if="row.status === 4">{{row.status_label}}</el-tag>
        </template>
      </el-table-column>
      <el-table-column :label="$t('bank_name')" min-width="260">
        <span slot-scope="{row}" v-if="row.type === 2 && !!row.member">{{row.member.bank_name}} {{row.member.bank_number}}</span>
      </el-table-column>
      <el-table-column :label="$t('operate')" align="center" min-width="190">
        <template slot-scope="{row}" v-if="row.status === 1 || row.status === 4">
          <el-button-group>
            <el-button type="success" size="mini" @click="patch(row.id, 3, $t('approve'))">{{$t('approve')}}</el-button>
            <el-button type="danger" size="mini" @click="patch(row.id, 2, $t('reject'))">{{$t('reject')}}</el-button>
            <el-button size="mini" v-if="row.status === 1" @click="patch(row.id, 4, $t('wait'))">{{$t('wait')}}</el-button>
          </el-button-group>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination
        class="page"
        :total="page.total"
        :page-size.sync="page.per_page"
        :current-page.sync="page.current"
        layout="->,total,sizes,prev,pager,next,jumper"
        :page-sizes="[10,20,30,40,50,100,500]"
        @size-change="getData"
        @current-change="getData"
    ></el-pagination>
  </div>
</template>

<script>
  import {mapState} from 'vuex'

  export default {
    name: 'finance',
    data() {
      return {
        list: [],
        search: {
          name: '',
          type: '',
          status: '',
          date: [],
          created_date: []
        },
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        isFirst: true
      }
    },
    computed: {
      ...mapState(['finance']),
      params() {
        let params = Object.assign({
          per_page: this.page.per_page,
          page: this.page.current,
          finance: true
        }, this.search)

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


        if (params.created_date) {
          if (params.created_date.length === 2) {
            params.created_date_start = params.created_date[0].format()
            params.created_date_end = params.created_date[1].format()
          }
          delete params.created_date
        }

        return params
      }
    },
    methods: {
      getData(init) {
        if (init === true) {
          this.page.current = 1
        }

        this.$api.get('/accounts', {params: this.params}).then(data => {
          if (data) {
            this.list = data.data
            this.page.total = Number(data.total)
          }
        })
      },
      patch(id, status, title) {
        this.$confirm(this.$t('finance_sq_confirm', [title]), title, {
          confirmButtonText: this.$t('btnYes'),
          cancelButtonText: this.$t('btnNo'),
          type: 'warning'
        }).then(() => {
          this.$api.patch(`/accounts/${id}`, {status}).then(data => {
            if (data) {
              this.getData()
              this.$message.success(this.$t('success'))
            }
          })
        }).catch(() => {})
      },
      rowClass({row}) {
        if (row.type === 2) {
          return 'withdrawal'
        }
      }
    },
    watch: {
      finance: {
        handler(v) {
          if (v.type !== '' || v.status !== '') {
            this.search = Object.assign(this.$options.data().search, v)
            this.$store.commit('setFinance', {type: '', status: '', date: []})
            if (!this.isFirst) {
              this.getData(true)
            }
          }
          if (this.isFirst) {
            this.getData(true)
          }
          this.isFirst = false
        },
        immediate: true
      }
    }
  }
</script>

<style lang="less">
  .el-table .withdrawal {
    background: oldlace;
  }
</style>