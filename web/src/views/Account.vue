<template>
  <div class="container">
    <el-form :inline="true" size="small" class="search">
      <el-form-item :label="$t('message.time')">
        <el-date-picker
            v-model="search.date"
            type="daterange"
            :start-placeholder="$t('base.time_start')"
            :end-placeholder="$t('base.time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item :label="$t('trade.type')">
        <el-select v-model="search.type" clearable>
          <el-option value="1" :label="$t('trade.type_1')"></el-option>
          <el-option value="2" :label="$t('trade.type_2')"></el-option>
          <el-option value="3" :label="$t('trade.type_3')"></el-option>
          <el-option value="4" :label="$t('trade.type_4')"></el-option>
          <el-option value="5" :label="$t('trade.type_5')"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="getData(true)" v-t="'base.search'"></el-button>
      </el-form-item>
    </el-form>
    <el-table :data="list" row-key="id">
      <el-table-column prop="updated_at" :label="$t('message.time')"></el-table-column>
      <el-table-column :label="$t('trade.type')">
        <template slot-scope="{row}">
          <span :class="`typel-${row.type}`">{{row.type_label}}</span>
        </template>
      </el-table-column>
      <el-table-column :label="$t('trade.amount')">
        <span slot-scope="{row}">{{row.amount|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('header.balance')">
        <span slot-scope="{row}">{{row.balance|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('trade.status')">
        <template slot-scope="{row}">
          <template v-if="row.type <= 2">
            <el-tag size="mini" v-if="row.status === 1">{{row.status_label}}</el-tag>
            <el-tag size="mini" type="danger" v-if="row.status === 2">{{row.status_label}}</el-tag>
            <el-tag size="mini" type="success" v-if="row.status === 3">{{row.status_label}}</el-tag>
            <el-tag size="mini" type="info" v-if="row.status === 4">{{row.status_label}}</el-tag>
          </template>
          <span v-else>-</span>
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
  export default {
    name: 'account',
    data() {
      return {
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        search: {
          date: null,
          type: ''
        },
        list: [],
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
    mounted() {
      this.getData()
    }
  }
</script>

<style lang="less">
  .typel-1 {
    color: #67C23A;
  }

  .typel-2 {
    color: #F56C6C;
  }

  .typel-3 {
    color: #409EFF;
  }

  .typel-4 {
    color: #E6A23C;
  }

  .type-5 {
    color: #606266;
  }
</style>
