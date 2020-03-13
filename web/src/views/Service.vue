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
    <div class="toolbar">
      <el-button type="success" size="small" @click="showAdd = true">+ {{$t('message.question')}}</el-button>
    </div>
    <el-table :data="list" row-key="id">
      <el-table-column prop="created_at" :label="$t('message.time')" width="180"></el-table-column>
      <el-table-column :label="$t('message.title')" width="600">
        <router-link class="atitle" slot-scope="{row}" :to="`/message/${$route.name}/${row.id}`">{{row.title}}</router-link>
      </el-table-column>
      <el-table-column :label="$t('message.author')">
        <span slot-scope="{row}">{{row.author ? row.author.nickname : ''}}</span>
      </el-table-column>
      <el-table-column prop="pageviews" :label="$t('message.pageviews')"></el-table-column>
      <el-table-column :label="$t('trade.status')">
        <template slot-scope="{row}">
          <span v-if="row.has_answer" style="color: #FD4322;" v-t="'message.newreply'"></span>
          <span v-else-if="row.is_solved" style="color: gray;" v-t="'message.solved'"></span>
          <span v-else style="color: #000;" v-t="'message.waitsolved'"></span>
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
    <el-dialog title="提问" :visible.sync="showAdd" custom-class="dialog-800">
      <el-form label-width="80px" :model="form" ref="addForm" @submit.native.prevent>
        <el-form-item :label="$t('message.title')" prop="title">
          <el-input v-model="form.title"></el-input>
        </el-form-item>
        <el-form-item :label="$t('message.content')" prop="content">
          <el-input v-model="form.content" type="textarea" :autosize="{minRows: 6}"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="showAdd = false" v-t="'base.btnNo'"></el-button>
        <el-button type="primary" v-t="'base.btnYes'" @click="submit"></el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
  export default {
    name: 'service',
    data() {
      return {
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        showAdd: false,
        form: {
          title: '',
          content: '',
          type: 1
        },
        list: [],
        search: {
          title: '',
          date: []
        },
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
      },
      submit() {
        this.$api.post('/messages', this.form).then(data => {
          if (data) {
            this.showAdd = false
            this.getData(true)
            this.$refs.addForm.resetFields()
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
  .toolbar {
    margin-bottom: 10px;
  }

  .atitle {
    color: #409EFF;

    &:hover {
      text-decoration: underline;
    }
  }
</style>
