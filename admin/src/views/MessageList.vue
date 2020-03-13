<template>
  <div>
    <el-form :inline="true" size="mini" class="search" label-width="80px">
      <el-form-item :label="$t('title')">
        <el-input v-model="search.title" clearable></el-input>
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
        <el-button type="primary" @click="getData(true)" v-t="'search'"></el-button>
      </el-form-item>
    </el-form>
    <div class="toolbar">
      <el-button-group>
      <el-button type="success" size="small" @click="show.update = true">+ {{$t('add')}}</el-button>
      <el-button type="danger" size="small" @click="del" :disabled="!hasSel" v-t="'del'"></el-button>
      </el-button-group>
    </div>
    <el-table
        :data="list"
        ref="table"
        border
        size="mini"
        row-key="id">
      <el-table-column type="selection"></el-table-column>
      <el-table-column prop="id" label="#"></el-table-column>
      <el-table-column :label="$t('author')">
        <span slot-scope="{row}">{{row.author ? row.author.nickname : ''}}</span>
      </el-table-column>
      <el-table-column :label="$t('title')" min-width="700">
        <span class="a" slot-scope="{row}" @click="edit(row)">{{row.title}}</span>
      </el-table-column>
      <el-table-column prop="created_at" :label="$t('time')" min-width="180"></el-table-column>
      <el-table-column prop="pageviews" :label="$t('pageviews')"></el-table-column>
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
    <el-dialog :title="!!form.id ? $t('edit') : $t('add')" :visible.sync="show.update" width="800px" @closed="resetForm">
      <el-form label-width="80px" @submit.native.prevent>
        <el-form-item :label="$t('title')">
          <el-input v-model="form.title"></el-input>
        </el-form-item>
        <el-form-item :label="$t('content')">
        <el-input v-model="form.content" type="textarea" :autosize="{minRows: 10}"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="show.update = false" v-t="'btnNo'"></el-button>
        <el-button type="primary" v-t="'btnYes'" @click="submit"></el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
  export default {
    name: 'message-list',
    props: {
      type: Number
    },
    data() {
      return {
        list: [],
        search: {
          title: '',
          date: []
        },
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        show: {
          update: false
        },
        isReady: false,
        form: {
          id: '',
          title: '',
          content: '',
          type: this.type
        }
      }
    },
    computed: {
      params() {
        let params = Object.assign({
          per_page: this.page.per_page,
          page: this.page.current,
          type: this.type
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
      },
      selIds() {
        let ids = []
        let sels = this.$refs.table.selection
        if (sels && sels.length > 0) {
          sels.forEach(s => {
            ids.push(s.id)
          })
        }
        return ids
      },
      hasSel() {
        return this.isReady && this.selIds.length > 0
      }
    },
    methods: {
      getData(init) {
        if (init === true) {
          this.page.current = 1
        }

        this.$api.get('/messages', {params: this.params}).then(data => {
          if (data) {
            this.list = data.data
            this.page.total = Number(data.total)
          }
        })
      },
      resetForm() {
        this.form = Object.assign({}, this.$options.data().form)
      },
      submit() {
        if (this.form.id > 0) {
          this.$api.put(`/messages/${this.form.id}`, this.form).then(data => {
            if (data) {
              this.show.update = false
              this.getData()
            }
          })
        } else {
          this.$api.post('/messages', this.form).then(data => {
            if (data) {
              this.show.update = false
              this.getData(true)
            }
          })
        }
      },
      edit(row) {
        let form = {}
        for (let i in this.form) {
          if (this.form.hasOwnProperty(i) && row.hasOwnProperty(i)) {
            form[i] = row[i]
          }
        }
        this.form = form
        this.show.update = true
      },
      del() {
        this.$confirm(this.$t('message_del_confirm'), this.$t('del'), {
          confirmButtonText: this.$t('btnYes'),
          cancelButtonText: this.$t('btnNo'),
          type: 'warning'
        }).then(() => {
          this.$api.delete('/messages', {params: {ids: this.selIds}}).then(data => {
            if (data) {
              this.getData()
              this.$message.success(this.$t('success'))
            }
          })
        }).catch(() => {})
      },
    },
    mounted() {
      this.isReady = true
      this.getData()
    }
  }
</script>
