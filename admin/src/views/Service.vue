<template>
  <div>
    <el-form :inline="true" size="mini" class="search" label-width="80px">
      <el-form-item :label="$t('question_new')" style="display: inline;float: right;">
        <el-switch v-model="search.has_question" @change="getData(true)"></el-switch>
      </el-form-item>
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
      <el-button type="danger" size="small" @click="del" :disabled="!hasSel" v-t="'del'"></el-button>
    </div>
    <el-table
        :data="list"
        ref="table"
        border
        size="mini"
        row-key="id">
      <el-table-column type="selection"></el-table-column>
      <el-table-column prop="id" label="#"></el-table-column>
      <el-table-column :label="$t('author')" min-width="140">
        <member slot-scope="{row}" :data="row.author"></member>
      </el-table-column>
      <el-table-column :label="$t('title')" min-width="500">
        <span class="a" slot-scope="{row}" @click="showDetail(row.id)">{{row.title}}</span>
      </el-table-column>
      <el-table-column prop="created_at" :label="$t('time')" min-width="180"></el-table-column>
      <el-table-column prop="pageviews" :label="$t('pageviews')"></el-table-column>
      <el-table-column :label="$t('status')">
        <template slot-scope="{row}">
          <el-tag size="mini" type="danger" v-if="row.has_question" v-t="'repy_new'"></el-tag>
          <el-tag size="mini" type="success" v-else-if="row.is_solved" v-t="'solved'"></el-tag>
          <el-tag size="mini" v-else v-t="'need_solved'"></el-tag>
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
    <el-dialog :title="$t('service')" :visible.sync="show.update" width="840px" @closed="close">
      <Detail v-if="show.detail || show.update" :id="updateId" @close="close"></Detail>
    </el-dialog>
  </div>
</template>

<script>
  import Detail from './ServiceDetail'
  import {mapState} from 'vuex'

  export default {
    name: 'service',
    components: {Detail},
    data() {
      return {
        list: [],
        search: {
          title: '',
          has_question: false,
          date: []
        },
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        show: {
          update: false,
          detail: false
        },
        isReady: false,
        updateId: 0,
        isFirst: true
      }
    },
    computed: {
      ...mapState(['question']),
      params() {
        let params = Object.assign({
          per_page: this.page.per_page,
          page: this.page.current,
          type: 1
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
    watch: {
      question: {
        handler(v) {
          if (v) {
            this.search.has_question = true
            this.$store.commit('setQuestion', false)
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
      del() {
        this.$confirm(this.$t('service_del_confirm'), this.$t('del'), {
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
      showDetail(id) {
        this.updateId = id
        this.show.update = true
        this.show.detail = true
      },
      close() {
        this.show.detail = false
        this.show.update = false
        this.getData()
      }
    },
    mounted() {
      this.isReady = true
    }
  }
</script>
