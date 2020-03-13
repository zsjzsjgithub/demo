<template>
  <div>
    <el-form :inline="true" size="mini" class="search" label-width="80px">
      <el-form-item :label="$t('look_online')" style="display: inline;float: right;">
        <el-switch v-model="search.online" @change="getData(true)"></el-switch>
      </el-form-item>
      <el-form-item :label="`${$t('username')}|${$t('nickname')}`">
        <el-input v-model="search.name" clearable></el-input>
      </el-form-item>
      <el-form-item :label="$t('menu_agent')" v-if="isAdmin">
        <el-select v-model="search.agent_id" filterable clearable>
          <el-option
              v-for="a in agents"
              :key="a.id"
              :label="`${a.nickname} (${a.username})`"
              :value="a.id">
          </el-option>
        </el-select>
      </el-form-item>
      <el-form-item :label="$t('time_reg')">
        <el-date-picker
            v-model="search.date"
            type="daterange"
            :start-placeholder="$t('time_start')"
            :end-placeholder="$t('time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item :label="$t('time_login')">
        <el-date-picker
            v-model="search.logged_date"
            type="daterange"
            :start-placeholder="$t('time_start')"
            :end-placeholder="$t('time_end')">
        </el-date-picker>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="getData(true)" v-t="'search'"></el-button>
      </el-form-item>
    </el-form>
    <div class="toolbar" v-if="isAdmin">
      <el-button-group>
        <el-button type="success" size="small" @click="show.update = true">+ {{$t('add')}}</el-button>
        <el-button type="danger" size="small" @click="del" :disabled="!hasSel" v-t="'del'"></el-button>
      </el-button-group>
      <el-button size="small" @click="exports" icon="el-icon-download" style="margin-left: 10px;" v-t="'export'"></el-button>
    </div>
    <el-table
        :data="list"
        ref="table"
        border
        stripe
        size="mini"
        row-key="id"
        :default-sort="{prop: 'id', order: 'descending'}"
        @sort-change="sortChange">
      <el-table-column type="selection" v-if="isAdmin"></el-table-column>
      <el-table-column prop="id" label="#" min-width="60" sortable="custom"></el-table-column>
      <el-table-column :label="$t('username')" v-if="isAdmin">
        <span class="a" slot-scope="{row}" @click="edit(row)">{{row.username}}</span>
      </el-table-column>
      <el-table-column v-else :label="$t('username')">
        <member slot-scope="{row}" :data="row"></member>
      </el-table-column>
      <el-table-column prop="nickname" :label="$t('nickname')" v-if="isAdmin"></el-table-column>
      <el-table-column :label="$t('menu_agent')" min-width="140" v-if="isAdmin">
        <template slot-scope="{row}" v-if="!!row.agent">
          <agent v-if="row.agent.type === 2" :data="row.agent"></agent>
          <member v-else :data="row.agent"></member>
        </template>
      </el-table-column>
      <el-table-column :label="$t('total_deposit')" min-width="100">
        <span slot-scope="{row}">{{row.deposit|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('total_withdrawal')" min-width="100">
        <span slot-scope="{row}">{{row.withdrawal|numFormat}}</span>
      </el-table-column>
      <el-table-column :label="$t('lr')" min-width="100" sortable="custom" prop="profit">
        <template slot-scope="{row}">
          <span style="color: #67C23A;" v-if="row.profit >= 0">{{(row.profit)|numFormat}}</span>
          <span style="color: #ff4949;" v-else>{{(row.profit)|numFormat}}</span>
        </template>
      </el-table-column>
      <el-table-column :label="$t('balance')" min-width="100" sortable="custom" prop="balance">
        <span slot-scope="{row}">{{row.balance|numFormat}}</span>
      </el-table-column>
      <el-table-column prop="created_at" :label="$t('time_reg')" min-width="140" sortable="custom"></el-table-column>
      <el-table-column prop="logged_at" :label="$t('time_login')" min-width="140" sortable="custom"></el-table-column>
      <el-table-column :label="$t('status')" min-width="140" v-if="isAdmin">
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
      <el-table-column v-else :label="$t('status')">
        <span slot-scope="{row}">{{row.is_enabled ? $t('enable'): $t('disable')}}</span>
      </el-table-column>
      <el-table-column v-if="isAdmin" :label="$t('operate')" min-width="180" align="center">
        <template slot-scope="{row}">
          <el-button-group>
            <el-button type="danger" size="mini" @click="() => {amountId = row.id; show.amount = true}" v-t="'change_balance'"></el-button>
            <el-button type="success" size="mini" v-t="'chat_send'" @click="openChat(row)"></el-button>
          </el-button-group>
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
    <el-dialog  v-if="isAdmin" :title="!!form.id ? $t('edit') : $t('add')" :visible.sync="show.update" width="500px" @closed="resetForm">
      <el-form label-width="80px" @submit.native.prevent>
        <el-form-item :label="$t('nickname')">
          <el-input v-model="form.nickname"></el-input>
        </el-form-item>
        <el-form-item :label="$t('username')">
          <el-input v-model="form.username" @change="check_username = false">
            <span slot="append" v-if="check_username" v-t="'successUser'" class="success"></span>
            <el-button slot="append" v-else v-t="'check'" @click="check"></el-button>
          </el-input>
        </el-form-item>
        <el-form-item :label="$t('password')">
          <el-input v-model="form.password"></el-input>
        </el-form-item>
        <el-form-item :label="$t('tel')">
          <el-input v-model="form.tel"></el-input>
        </el-form-item>
        <el-form-item :label="$t('bank_name')">
          <el-input v-model="form.bank_name"></el-input>
        </el-form-item>
        <el-form-item :label="$t('bank_number')">
          <el-input v-model="form.bank_number"></el-input>
        </el-form-item>
        <el-form-item :label="$t('agent_name')">
          <el-select v-model="form.agent_id" filterable>
            <el-option
                v-for="a in agents"
                :key="a.id"
                :label="`${a.nickname} (${a.username})`"
                :value="a.id">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="show.update = false" v-t="'btnNo'"></el-button>
        <el-button type="primary" v-t="'btnYes'" @click="submit"></el-button>
      </div>
    </el-dialog>
    <el-dialog v-if="isAdmin" :title="$t('change_balance')" :visible.sync="show.amount" width="400px" @closed="amount = 0">
      <el-input v-model="amount"></el-input>
      <p style="margin-top: 10px;font-size: 12px;color: #F56C6C;" v-t="'change_balance_tips'"></p>
      <div slot="footer" class="dialog-footer">
        <el-button @click="show.amount = false" v-t="'btnNo'"></el-button>
        <el-button type="primary" v-t="'btnYes'" @click="changeAmount"></el-button>
      </div>
    </el-dialog>
    <el-dialog :title="$t('chat_title', [chat.name])" :visible.sync="show.chat" width="600px" @closed="clearChat">
      <el-row :gutter="12" style="margin-bottom: 20px;">
        <el-col :span="21">
          <el-input size="mini" v-model="chat.content"></el-input>
        </el-col>
        <el-col :span="3">
          <el-button size="mini" type="primary" v-t="'chat_btn'" @click="chatSend"></el-button>
        </el-col>
      </el-row>
      <el-table
          :data="chats"
          ref="table2"
          :show-header="false"
          size="mini"
          row-key="id">
        <el-table-column align="right" width="140">
          <span slot-scope="{row}" style="color: #99a9bf;">{{row.created_at}}</span>
        </el-table-column>
        <el-table-column align="right" width="70">
          <span slot-scope="{row}" style="color: #99a9bf;">{{row.author.nickname}}</span>
        </el-table-column>
        <el-table-column>
          <template slot-scope="{row}">
            <el-tag type="success" size="mini" v-if="!row.is_read" v-t="'chat_unread'"></el-tag>
            {{row.content}}
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
          class="page"
          :total="page2.total"
          :page-size.sync="page2.per_page"
          :current-page.sync="page2.current"
          layout="->,prev,pager,next"
          :page-sizes="[10,20,30,40,50,100,500]"
          @size-change="getChats"
          @current-change="getChats"
      ></el-pagination>
    </el-dialog>
  </div>
</template>

<script>
  import {agents} from '../datas';
  import {mapState} from 'vuex'

  export default {
    name: 'home',
    data() {
      return {
        list: [],
        chats: [],
        chat: {
          id: 0,
          name: '',
          content: ''
        },
        search: {
          name: '',
          agent_id: '',
          online: false,
          date: [],
          logged_date: []
        },
        page: {
          total: 0,
          per_page: 10,
          current: 1
        },
        page2: {
          total: 0,
          per_page: 10,
          current: 1
        },
        amount: 0,
        amountId: 0,
        show: {
          update: false,
          amount: false,
          chat: false
        },
        isReady: false,
        check_username: false,
        agents: [],
        form: {
          id: '',
          nickname: '',
          username: '',
          password: '',
          tel: '',
          bank_name: '',
          bank_number: '',
          agent_id: ''
        },
        isFirst: true,
        orderBy: 'id',
        orderType: 'desc'
      }
    },
    computed: {
      ...mapState(['mem', 'isAdmin']),
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
            if (typeof params.date[0] === 'string') {
              params.date_start = params.date[0]
              params.date_end = params.date[1]
            } else {
              params.date_start = params.date[0].format()
              params.date_end = params.date[1].format()
            }
          }
          delete params.date
        }

        if (params.logged_date) {
          if (params.logged_date.length === 2) {
            params.logged_date_start = params.logged_date[0].format()
            params.logged_date_end = params.logged_date[1].format()
          }
          delete params.logged_date
        }

        // sort
        params.order_by = this.orderBy
        params.order_type = this.orderType

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

        this.$api.get('/members', {params: this.params}).then(data => {
          if (data) {
            this.list = data.data
            this.page.total = Number(data.total)
          }
        })
      },
      resetForm() {
        this.check_username = false
        this.form = Object.assign({}, this.$options.data().form)
      },
      del() {
        this.$confirm(this.$t('member_del_confirm'), this.$t('del'), {
          confirmButtonText: this.$t('btnYes'),
          cancelButtonText: this.$t('btnNo'),
          type: 'warning'
        }).then(() => {
          this.$api.delete('/members', {params: {ids: this.selIds}}).then(data => {
            if (data) {
              this.getData()
              this.$message.success(this.$t('success'))
            }
          })
        }).catch(() => {})
      },
      submit() {
        if (this.form.id > 0) {
          this.$api.put(`/members/${this.form.id}`, this.form).then(data => {
            if (data) {
              this.show.update = false
              this.getData()
            }
          })
        } else {
          this.$api.post('/tokens/register', Object.assign(this.form, {password_confirmation: this.form.password})).then(data => {
            if (data) {
              this.show.update = false
              this.getData(true)
            }
          })
        }
      },
      changeAmount() {
        this.$api.put(`/balances/${this.amountId}`, {amount: this.amount}).then(data => {
          if (data) {
            this.show.amount = false
            this.getData(true)
          }
        })
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
      check() {
        let params = {username: this.form.username}
        if (this.form.id > 0) {
          params.id = this.form.id
        }

        this.$api.get('/tokens/check', {params}).then(data => {
          if (data && data.check) {
            this.check_username = true
          }
        })
      },
      toggleEnable({id, is_enabled}) {
        this.$api.patch(`/members/${id}`, {is_enabled}).then(data => {
          if (data) {
            this.getData()
          }
        })
      },
      exports() {
        global.location.href = process.env.VUE_APP_API_HOST + '/members/export?token=' + this.$store.state.token.token
      },
      clearChat() {
        this.chat = {
          id: 0,
          name: '',
          content: ''
        }
        this.chats = []
      },
      getChats(init) {
        if (init === true) {
          this.page2.current = 1
        }

        let params = {
          per_page: this.page2.per_page,
          page: this.page2.current,
          member_id: this.chat.id
        }

        this.$api.get('/chats', {params}).then(data => {
          if (data) {
            this.chats = data.data
            this.page2.total = Number(data.total)
          }
        })
      },
      openChat(row) {
        this.chat.id = row.id
        this.chat.name = row.nickname
        this.show.chat = true
        this.getChats(true)
      },
      chatSend() {
        this.$api.post('/chats', {
          member_id: this.chat.id,
          content: this.chat.content
        }).then(data => {
          if (data) {
            this.chat.content = ''
            this.getChats(true)
          }
        })
      },
      sortChange({prop, order}) {
        this.orderBy = prop || 'id'
        this.orderType = order === 'ascending' ? 'asc' : 'desc';
        this.getData(true)
      }
    },
    watch: {
      mem: {
        handler(v) {
          if (v.online !== false || v.date.length === 2) {
            this.search = Object.assign(this.$options.data().search, v)
            this.$store.commit('setMem', {online: false, date: []})
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
    mounted() {
      this.isReady = true
      agents().then(data => {
        if (data) {
          this.agents = data
        }
      })
    }
  }
</script>

