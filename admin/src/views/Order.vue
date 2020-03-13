<template>
  <div>
    <el-form :inline="true" size="mini" class="search" label-width="80px">
      <el-form-item :label="$t('jxz')" style="display: inline;float: right;">
        <el-switch :value="search.status === '1'" @change="changeSwitch"></el-switch>
      </el-form-item>
      <el-form-item :label="`${$t('username')}|${$t('nickname')}`">
        <el-input v-model="search.name" clearable></el-input>
      </el-form-item>
      <el-form-item :label="$t('sn')">
        <el-input v-model="search.sn" clearable></el-input>
      </el-form-item>
      <el-form-item :label="$t('type')">
        <el-select v-model="search.type" clearable>
          <el-option value="1" label="BUY"></el-option>
          <el-option value="2" label="SELL"></el-option>
          <el-option value="3" label="CANCEL"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item :label="$t('status')">
        <el-select v-model="search.status" clearable>
          <el-option value="1" :label="$t('jxz')"></el-option>
          <el-option value="2" :label="$t('win')"></el-option>
          <el-option value="3" :label="$t('lose')"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item :label="$t('scene')">
        <el-date-picker
            v-model="search.scene_date"
            type="daterange"
            :start-placeholder="$t('time_start')"
            :end-placeholder="$t('time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item :label="$t('time_order')">
        <el-date-picker
            v-model="search.date"
            type="daterange"
            :start-placeholder="$t('time_start')"
            :end-placeholder="$t('time_end')">
        </el-date-picker>
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
      <el-table-column prop="id" label="#" min-width="60"></el-table-column>
      <el-table-column prop="sn" :label="$t('sn')" min-width="130"></el-table-column>
      <el-table-column :label="$t('member')" min-width="140">
        <member slot-scope="{row}" :data="row.member"></member>
      </el-table-column>
      <el-table-column prop="scene_time" :label="$t('scene')" min-width="140"></el-table-column>
      <el-table-column prop="open" :label="$t('price')"></el-table-column>
      <el-table-column :label="$t('type')">
        <span slot-scope="{row}" :class="`type-${row.type}`">{{row.type_label}}</span>
      </el-table-column>
      <el-table-column :label="$t('amount')">
        <span slot-scope="{row}">{{row.amount|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('status')" width="100">
        <span slot-scope="{row}" :class="`status-${row.status}`">{{row.status_label}} <span v-if="row.status===2">{{row.rate * row.amount}}</span></span>
      </el-table-column>
      <el-table-column prop="created_at" :label="$t('time_buy')" min-width="140"></el-table-column>
      <el-table-column prop="updated_at" :label="$t('time_js')" min-width="140">
        <span slot-scope="{row}" v-if="row.status !== 1">{{row.updated_at}}</span>
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
    name: 'order',
    data() {
      return {
        list: [],
        search: {
          name: '',
          sn: '',
          type: '',
          status: '',
          scene_date: [],
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

        if (params.scene_date) {
          if (params.scene_date.length === 2) {
            params.scene_date_start = params.scene_date[0].format()
            params.scene_date_end = params.scene_date[1].format()
          }
          delete params.scene_date
        }

        return params
      }
    },
    methods: {
      getData(init) {
        if (init === true) {
          this.page.current = 1
        }

        this.$api.get('/orders', {params: this.params}).then(data => {
          if (data) {
            this.list = data.data
            this.page.total = Number(data.total)
          }
        })
      },
      changeSwitch() {
        if (this.search.status === '1') {
          this.search.status = ''
        } else {
          this.search.status = '1'
        }
        this.getData()
      }
    },
    mounted() {
      this.getData()
    }
  }
</script>

<style scoped lang="less">
  @import "../assets/config";

  .type-1,.status-3 {
    color: @color_buy;
  }
  .type-2,.status-2 {
    color: @color_sell;
  }
  .type-3 {
    color: @color_cancel;
  }
</style>