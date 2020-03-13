<template>
  <div class="container">
    <el-form :inline="true" size="small" class="search">
      <el-form-item :label="$t('trade.sn')">
        <el-input v-model="search.sn" clearable></el-input>
      </el-form-item>
      <el-form-item :label="$t('trade.type')">
        <el-select v-model="search.type" clearable>
          <el-option value="1" label="BUY"></el-option>
          <el-option value="2" label="SELL"></el-option>
          <el-option value="3" label="CANCEL"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item :label="$t('trade.status')">
        <el-select v-model="search.status" clearable>
          <el-option value="1" :label="$t('trade.jxz')"></el-option>
          <el-option value="2" :label="$t('trade.win')"></el-option>
          <el-option value="3" :label="$t('trade.lose')"></el-option>
        </el-select>
      </el-form-item>
      <br>
      <el-form-item :label="$t('trade.scene')">
        <el-date-picker
            v-model="search.scene_date"
            type="daterange"
            :start-placeholder="$t('base.time_start')"
            :end-placeholder="$t('base.time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item :label="$t('base.order_time')">
        <el-date-picker
            v-model="search.date"
            type="daterange"
            :start-placeholder="$t('base.time_start')"
            :end-placeholder="$t('base.time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="getData(true)" v-t="'base.search'"></el-button>
      </el-form-item>
    </el-form>
    <el-table :data="list" default-expand-all class="eltb" row-key="id" :row-class-name="rowClass">
      <el-table-column prop="sn" width="140" :label="$t('trade.sn')"></el-table-column>
      <el-table-column prop="scene_time" width="160" :label="$t('trade.scene')"></el-table-column>
      <el-table-column :label="$t('trade.amount')">
        <span slot-scope="{row}">{{row.amount|numFormat}}</span>
      </el-table-column>
      <el-table-column prop="rate" :label="$t('trade.rate')"></el-table-column>
      <el-table-column prop="open" :label="$t('trade.price')"></el-table-column>
      <el-table-column :label="$t('trade.status')">
        <span slot-scope="{row}" :class="`status-${row.status}`">{{row.status_label}} <span v-if="row.status===2">{{row.rate * row.amount}}</span></span>
      </el-table-column>
      <el-table-column :label="$t('trade.type')">
        <span slot-scope="{row}" :class="`type-${row.type}`">{{row.type_label}}</span>
      </el-table-column>
      <el-table-column prop="created_at" width="160" :label="$t('base.buy_time')"></el-table-column>
      <el-table-column type="expand" width="0">
        <div slot-scope="{row}" class="jiesuan" v-if="row.status !== 1">
          <label>{{$t('base.js_time')}}：</label>
          <span>{{row.updated_at}}</span>
          <label>{{$t('trade.open')}}：</label>
          <span>{{row.forex_data.open}}</span>
          <label>{{$t('trade.high')}}：</label>
          <span>{{row.forex_data.high}}</span>
          <label>{{$t('trade.low')}}：</label>
          <span>{{row.forex_data.low}}</span>
          <label>{{$t('trade.close')}}：</label>
          <span>{{row.forex_data.close}}</span>
        </div>
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
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        search: {
          sn: '',
          type: '',
          status: '',
          date: null,
          scene_date: null
        },
        list: [],
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
      rowClass({row}) {
        if (row.status === 2) {
          return 'row-win'
        } else if (row.status === 3) {
          return 'row-lose'
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
  .jiesuan {
    padding: 0 20px;
    font-size: 12px;
    overflow: auto;
    line-height: 22px;
    background: #fcfcfc;

    label {
      .fl;
      color: #99a9bf;
    }

    span {
      .fl;
      margin-left: 6px;
      margin-right: 30px;
    }
  }
  
  .eltb {
    border: 1px solid #ebeef5;
    border-bottom: none;
  }
</style>

<style lang="less">
  .el-table {
    .row-win {
      background: #f0f9eb;
    }

    .row-lose {
      background: oldlace;
    }
  }
</style>