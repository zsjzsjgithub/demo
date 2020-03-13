<template>
  <div>
    <el-form :inline="true" size="mini" class="search" label-width="80px">
      <el-form-item :label="`${$t('username')}|${$t('nickname')}`">
        <el-input v-model="search.name" clearable></el-input>
      </el-form-item>
      <el-form-item :label="$t('time_login')">
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
        stripe
        size="mini"
        row-key="id">
      <el-table-column type="selection"></el-table-column>
      <el-table-column prop="id" label="#" min-width="60"></el-table-column>
      <el-table-column :label="$t('username')">
        <span class="a" slot-scope="{row}" @click="edit(row)">{{row.username}}</span>
      </el-table-column>
      <el-table-column prop="nickname" :label="$t('nickname')"></el-table-column>
      <el-table-column prop="members_count" :label="$t('member_count')"></el-table-column>
      <el-table-column :label="$t('deposit_sum')">
        <span slot-scope="{row}">{{row.deposit_sum|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('withdrawal_sum')">
        <span slot-scope="{row}">{{row.withdrawal_sum|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('lr_sum')">
        <template slot-scope="{row}">
          <span style="color: #67C23A;" v-if="Number(row.deposit_sum) - Number(row.withdrawal_sum) >= 0">
            {{Number(row.deposit_sum) - Number(row.withdrawal_sum)|numFormat}}
          </span>
          <span style="color: #ff4949;" v-else>{{Number(row.deposit_sum) - Number(row.withdrawal_sum)|numFormat}}</span>
        </template>
      </el-table-column>
      <el-table-column :label="$t('commission_rate')">
        <span slot-scope="{row}">{{row.commission_rate}}%</span>
      </el-table-column>
      <el-table-column :label="$t('sr_sum')">
        <template slot-scope="{row}">
          <span style="color: #67C23A;" v-if="Number(row.deposit_sum) - Number(row.withdrawal_sum) >= 0">
            {{(Number(row.deposit_sum) - Number(row.withdrawal_sum)) * row.commission_rate / 100|numFormat}}
          </span>
          <span style="color: #ff4949;" v-else>{{(Number(row.deposit_sum) - Number(row.withdrawal_sum)) * row.commission_rate / 100|numFormat}}</span>
        </template>
      </el-table-column>
      <el-table-column prop="balance_sum" :label="$t('balance_sum')">
        <span slot-scope="{row}">{{row.balance_sum|numFormat}}</span>
      </el-table-column>
      <el-table-column prop="created_at" :label="$t('time_join')" min-width="140"></el-table-column>
      <el-table-column prop="logged_at" :label="$t('time_login')" min-width="140"></el-table-column>
      <el-table-column :label="$t('status')" min-width="140">
        <template slot-scope="{row}">
          <el-switch
              v-model="row.is_enabled"
              active-color="#13ce66"
              inactive-color="#ff4949"
              :active-text="$t('enable')"
              :inactive-text="$t('disable')"
              @change="toggleEnable(row)">
          </el-switch>
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
    <el-dialog :title="!!form.id ? $t('edit') : $t('add')" :visible.sync="show.update" width="500px" @closed="resetForm">
      <el-form label-width="80px" @submit.native.prevent>
        <el-form-item :label="$t('nickname')">
          <el-input v-model="form.nickname"></el-input>
        </el-form-item>
        <el-form-item :label="$t('username')">
          <el-input v-model="form.username"></el-input>
        </el-form-item>
        <el-form-item :label="$t('password')">
          <el-input v-model="form.password"></el-input>
        </el-form-item>
        <el-form-item :label="$t('tel')">
          <el-input v-model="form.tel"></el-input>
        </el-form-item>
        <el-form-item :label="$t('commission_rate')">
          <el-input v-model="form.commission_rate"></el-input>
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
    name: 'agent',
    data() {
      return {
        isReady: false,
        list: [],
        search: {
          name: '',
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
        form: {
          id: '',
          nickname: '',
          username: '',
          password: '',
          tel: '',
          commission_rate: ''
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

        this.$api.get('/agents', {params: this.params}).then(data => {
          if (data) {
            this.list = data.data
            this.page.total = Number(data.total)
          }
        })
      },
      resetForm() {
        this.form = Object.assign({}, this.$options.data().form)
      },
      del() {
        this.$confirm(this.$t('agent_del_confirm'), this.$t('del'), {
          confirmButtonText: this.$t('btnYes'),
          cancelButtonText: this.$t('btnNo'),
          type: 'warning'
        }).then(() => {
          this.$api.delete('/agents', {params: {ids: this.selIds}}).then(data => {
            if (data) {
              this.getData()
              this.$message.success(this.$t('success'))
            }
          })
        }).catch(() => {})
      },
      submit() {
        if (this.form.id > 0) {
          this.$api.put(`/agents/${this.form.id}`, this.form).then(data => {
            if (data) {
              this.show.update = false
              this.getData(true)
            }
          })
        } else {
          this.$api.post('/agents', this.form).then(data => {
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
      toggleEnable({id, is_enabled}) {
        this.$api.patch(`/agents/${id}`, {is_enabled}).then(data => {
          if (data) {
            this.getData()
          }
        })
      }
    },
    mounted() {
      this.getData()
      this.isReady = true
    }
  }
</script>

