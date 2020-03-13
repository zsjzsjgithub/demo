<template>
  <div class="container">
    <div class="senddiv">
      <div class="int">
        <el-input v-model="content"></el-input>
      </div>
      <div class="btn">
        <el-button type="primary" v-t="'chat.send'" @click="chatSend"></el-button>
      </div>
    </div>
    <el-table :data="list" row-key="id" :show-header="false">
      <el-table-column align="right" width="160">
        <span slot-scope="{row}" style="color: #99a9bf;">{{row.created_at}}</span>
      </el-table-column>
      <el-table-column align="right" width="120">
        <span slot-scope="{row}" style="color: #99a9bf;">{{row.author.nickname}}</span>
      </el-table-column>
      <el-table-column>
        <template slot-scope="{row}">
          <el-tag type="success" size="mini" v-if="!row.is_read" v-t="'chat.unread'"></el-tag>
          {{row.content}}
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
    name: 'chat',
    data() {
      return {
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        list: [],
        content: ''
      }
    },
    methods: {
      getData(init) {
        if (init === true) {
          this.page.current = 1
        }
        this.$api.get('/chats', {params: this.params}).then(data => {
          if (data) {
            this.list = data.data
            this.page.total = Number(data.total)
          }
        })
      },
      chatSend() {
        this.$api.post('/chats', {
          content: this.content
        }).then(data => {
          if (data) {
            this.getData(true)
            this.content = ''
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
        return params
      }
    },
    watch: {
      '$route.query.t'() {
        this.getData()
      }
    },
    mounted() {
      this.getData()
    }
  }
</script>

<style scoped lang="less">
  .senddiv {
    margin-bottom: 30px;
    .int {
      display: inline-block;
      width: 80%;
      margin-right: 20px;
    }

    .btn {
      display: inline-block;
    }
  }
</style>
