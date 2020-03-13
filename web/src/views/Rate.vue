<template>
  <div class="container">
    <el-form :inline="true" size="small" class="search">
      <el-form-item :label="$t('message.time')">
        <el-date-picker
            v-model="search.time"
            type="datetimerange"
            :start-placeholder="$t('base.time_start')"
            :end-placeholder="$t('base.time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="getData(true)" v-t="'base.search'"></el-button>
      </el-form-item>
    </el-form>
    <el-table :data="list" row-key="id">
      <el-table-column prop="time"  :label="$t('message.time')"></el-table-column>
      <el-table-column prop="type_label" :label="$t('trade.project')"></el-table-column>
      <el-table-column prop="open" :label="$t('trade.open')"></el-table-column>
      <el-table-column prop="high" :label="$t('trade.high')"></el-table-column>
      <el-table-column prop="low"  :label="$t('trade.low')"></el-table-column>
      <el-table-column prop="close"  :label="$t('trade.close')"></el-table-column>
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
    name: 'rate',
    data() {
      return {
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        search: {
          time: null
        },
        list: []
      }
    },
    methods: {
      getData(init) {
        if (init === true) {
          this.page.current = 1
        }
        this.$api.get('/forexes', {params: this.params}).then(data => {
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

        if (params.time) {
          if (params.time.length === 2) {
            params.time_start = params.time[0].format()
            params.time_end = params.time[1].format()
          }
          delete params.time
        }
        return params
      }
    },
    mounted() {
      this.getData()
    }
  }
</script>
