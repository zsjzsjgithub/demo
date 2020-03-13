<template>
  <div>
    <el-form :inline="true" size="mini" class="search" label-width="80px">
      <el-form-item :label="`${$t('username')}|${$t('nickname')}`">
        <el-input v-model="search.name" clearable></el-input>
      </el-form-item>
      <el-form-item :label="$t('time')">
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
          <el-option value="3" :label="$t('type_3')"></el-option>
          <el-option value="4" :label="$t('type_4')"></el-option>
          <el-option value="5" :label="$t('type_5')"></el-option>
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
        stripe
        size="mini"
        row-key="id">
      <el-table-column prop="id" label="#"></el-table-column>
      <el-table-column :label="$t('member')">
        <member slot-scope="{row}" :data="row.member"></member>
      </el-table-column>
      <el-table-column prop="updated_at" :label="$t('time')" min-width="140"></el-table-column>
      <el-table-column prop="type_label" :label="$t('type')"></el-table-column>
      <el-table-column :label="$t('status')">
        <template slot-scope="{row}" v-if="row.type <= 2">
          <el-tag size="mini" v-if="row.status === 1">{{row.status_label}}</el-tag>
          <el-tag size="mini" type="danger" v-if="row.status === 2">{{row.status_label}}</el-tag>
          <el-tag size="mini" type="success" v-if="row.status === 3">{{row.status_label}}</el-tag>
          <el-tag size="mini" type="info" v-if="row.status === 4">{{row.status_label}}</el-tag>
        </template>
      </el-table-column>
      <el-table-column :label="$t('amount')">
        <span slot-scope="{row}">{{row.amount|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('now_balance')">
        <span slot-scope="{row}">{{row.balance|numFormat}}</span>
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
  export default {
    name: 'account',
    data() {
      return {
        list: [],
        search: {
          name: '',
          type: '',
          date: []
        },
        page: {
          total: 0,
          per_page: 10,
          current: 1
        }
      }
    },
    computed: {
      params() {
        let params = Object.assign({
          per_page: this.page.per_page,
          page: this.page.current
        }, this.search)

        for (let p in params) {
          if (!params[p]) {
            delete params[p]
          }
        }

        if (params.date) {
          if (params.date.length === 2) {
            params.date_start = params.date[0].format()
            params.date_end = params.date[1].format()
          }
          delete params.date
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
      }
    },
    mounted() {
      this.getData()
    }
  }
</script>
