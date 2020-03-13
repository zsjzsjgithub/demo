<template>
  <div>
    <el-form :inline="true" size="small" class="search">
      <el-form-item :label="$t('message.title')">
        <el-input v-model="search.title" clearable></el-input>
      </el-form-item>
      <el-form-item :label="$t('message.date')">
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
    <el-table :data="list" row-key="id">
      <el-table-column prop="created_at" :label="$t('message.time')" width="180"></el-table-column>
      <el-table-column :label="$t('message.title')" width="700">
        <router-link class="atitle" slot-scope="{row}" :to="`/message/${$route.name}/${row.id}`">{{row.title}}</router-link>
      </el-table-column>
      <el-table-column :label="$t('message.author')">
        <span slot-scope="{row}">{{row.author ? row.author.nickname : ''}}</span>
      </el-table-column>
      <el-table-column prop="pageviews" :label="$t('message.pageviews')"></el-table-column>
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
    name: 'message-list',
    data() {
      return {
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        search: {
          title: '',
          date: []
        },
        list: [],
      }
    },
    methods: {
      getData(init) {
        if (init === true) {
          this.page.current = 1
        }

        let params = Object.assign({
          per_page: this.page.per_page,
          page: this.page.current,
          type: this.$route.meta.type
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

        this.$api.get('/messages', {params}).then(data => {
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

<style scoped lang="less">
  .atitle {
    color: #409EFF;

    &:hover {
      text-decoration: underline;
    }
  }
</style>
