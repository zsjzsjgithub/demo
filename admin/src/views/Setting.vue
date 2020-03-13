<template>
  <div>
    <el-tabs type="card" v-model="tabName">
      <el-tab-pane :label="$t('base_setting')" name="trade">
        <el-form label-width="100px" style="width: 500px">
          <el-form-item :label="`Buy${$t('rate')}`">
            <el-input v-model="form.rate_1"></el-input>
          </el-form-item>
          <el-form-item :label="`Sell${$t('rate')}`">
            <el-input v-model="form.rate_2"></el-input>
          </el-form-item>
          <el-form-item :label="`Cancel${$t('rate')}`">
            <el-input v-model="form.rate_3"></el-input>
          </el-form-item>
          <el-form-item :label="$t('jiage')" class="form-price">
            <el-row :gutter="10">
              <el-col :span="8">
                <el-input v-model="form.price_1"></el-input>
              </el-col>
              <el-col :span="8">
                <el-input v-model="form.price_2"></el-input>
              </el-col>
              <el-col :span="8">
                <el-input v-model="form.price_3"></el-input>
              </el-col>
            </el-row>
            <el-row :gutter="10" style="margin-top: 10px;">
              <el-col :span="8">
                <el-input v-model="form.price_4"></el-input>
              </el-col>
              <el-col :span="8">
                <el-input v-model="form.price_5"></el-input>
              </el-col>
              <el-col :span="8">
                <el-input v-model="form.price_6"></el-input>
              </el-col>
            </el-row>
          </el-form-item>
          <el-form-item :label="$t('first_rate')">
            <el-input v-model="form.first_rate"></el-input>
          </el-form-item>
          <el-form-item :label="$t('range')">
            <el-input v-model="form.range"></el-input>
          </el-form-item>
          <el-form-item :label="$t('open')">
            <el-switch v-model="form.allow_register"></el-switch>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="update" v-t="'save'"></el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
      <el-tab-pane :label="$t('firewall')" name="firewall">
        <h4 class="h4" v-t="'admin_ip'"></h4>
        <el-form label-width="100px" style="width: 500px">
          <el-form-item :label="`IP${i+1}`" v-for="(ip, i) in ips" :key="i">
            <el-input v-model="ips[i]">
              <el-button slot="suffix" size="small" type="danger" v-if="ips.length > 1" @click="delIp(i)" v-t="'del'"></el-button>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="success" @click="addIp" v-t="'add'"></el-button>
            <el-button type="primary" @click="updateIp" v-t="'save'"></el-button>
          </el-form-item>
        </el-form>
        <h4 class="h4 mt" v-t="'login_log'"></h4>
        <div class="ip-list">
          <p v-for="l in logs" :key="l.id">
            {{l.time}}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            {{l.ip}}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <template>
              <el-tag size="mini" type="success" v-if="l.success" v-t="'succ'"></el-tag>
              <el-tag size="mini" type="danger" v-else v-t="'shibai'"></el-tag>
            </template>
          </p>
        </div>
      </el-tab-pane>
      <el-tab-pane :label="$t('pop')" name="pop">
        <div class="toolbar">
          <el-button type="success" size="small" @click="() => {show.update = true;show.edit = true}">+ {{$t('add')}}</el-button>
        </div>
        <el-table
            :data="pops"
            size="mini"
            row-key="id">
          <el-table-column prop="id" label="#"></el-table-column>
          <el-table-column :label="$t('title')">
            <span class="a" slot-scope="{row}" @click="edit(row)">{{row.title}}</span>
          </el-table-column>
          <el-table-column :label="$t('width')">
            <span slot-scope="{row}">{{row.width}}px</span>
          </el-table-column>
          <el-table-column :label="$t('height')">
            <span slot-scope="{row}">{{row.height}}px</span>
          </el-table-column>
          <el-table-column label="X">
            <template slot-scope="{row}">
              <span v-if="row.xt === 'center'">{{row.xt}}</span>
              <span v-else>{{row.xt}}:{{row.x}}px</span>
            </template>
          </el-table-column>
          <el-table-column label="Y">
            <template slot-scope="{row}">
              <span v-if="row.yt === 'center'">{{row.yt}}</span>
              <span v-else>{{row.yt}}:{{row.y}}px</span>
            </template>
          </el-table-column>
          <el-table-column :label="$t('status')" min-width="140">
            <template slot-scope="{row}">
              <el-switch
                  v-model="row.is_enable"
                  active-color="#13ce66"
                  inactive-color="#ff4949"
                  :active-text="$t('enable')"
                  :inactive-text="$t('disable')"
                  @change="toggleEnable(row)">
              </el-switch>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
      <el-tab-pane :label="$t('clear')" name="clear">
        <el-form label-width="160px">
          <el-form-item :label="$t('reset_member_login')">
            <el-button size="small" type="danger" @click="resetData(1)" v-t="'reset'"></el-button>
          </el-form-item>
          <el-form-item :label="$t('del_member_order')">
            <el-button size="small" type="danger" @click="resetData(2)" v-t="'del'"></el-button>
          </el-form-item>
          <el-form-item :label="$t('del_member_account')">
            <el-button size="small" type="danger" @click="resetData(3)" v-t="'del'"></el-button>
          </el-form-item>
          <el-form-item :label="$t('del_member_finance')">
            <el-button size="small" type="danger" @click="resetData(4)" v-t="'del'"></el-button>
          </el-form-item>
          <el-form-item :label="$t('del_member_question')">
            <el-button size="small" type="danger" @click="resetData(5)" v-t="'del'"></el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
    </el-tabs>
    <el-dialog :title="!!pop.id ? $t('edit') : $t('add')" :visible.sync="show.update" width="900px" @closed="resetForm">
      <el-form label-width="60px" @submit.native.prevent size="mini">
        <el-form-item :label="$t('title')">
          <el-input v-model="pop.title"></el-input>
        </el-form-item>
        <el-row :gutter="10">
          <el-col :span="12">
            <el-form-item label="X">
              <el-input v-model="pop.x" class="input-with-select" :disabled="pop.xt === 'center'">
                <el-select v-model="pop.xt" slot="prepend">
                  <el-option value="left" :label="$t('left')"></el-option>
                  <el-option value="center" :label="$t('center')"></el-option>
                  <el-option value="right" :label="$t('right')"></el-option>
                </el-select>
                <span slot="append">px</span>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Y">
              <el-input v-model="pop.y" class="input-with-select" :disabled="pop.yt === 'center'">
                <el-select v-model="pop.yt" slot="prepend">
                  <el-option value="top" :label="$t('top')"></el-option>
                  <el-option value="center" :label="$t('center')"></el-option>
                  <el-option value="bottom" :label="$t('bottom')"></el-option>
                </el-select>
                <span slot="append">px</span>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="10">
          <el-col :span="12">
            <el-form-item :label="$t('width')">
              <el-input v-model="pop.width">
                <span slot="append">px</span>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item :label="$t('height')">
              <el-input v-model="pop.height">
                <span slot="append">px</span>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item :label="$t('content')">
          <Editor ref="editor" v-model="pop.content" v-if="show.edit"></Editor>
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
  import Editor from '../components/Editor'

  export default {
    name: 'setting',
    components: {Editor},
    data() {
      return {
        tabName: 'trade',
        form: {
          rate_1: 0,
          rate_2: 0,
          rate_3: 0,
          price_1: 0,
          price_2: 0,
          price_3: 0,
          price_4: 0,
          price_5: 0,
          price_6: 0,
          first_rate: 0,
          range: 0,
          allow_register: true
        },
        ips: [],
        logs: [],
        pops: [],
        pop: {
          id: '',
          title: '',
          x: '',
          xt: 'left',
          y: '',
          yt: 'top',
          width: '',
          height: '',
          content: ''
        },
        show: {
          update: false,
          edit: false
        },
      }
    },
    watch: {
      tabName(v) {
        if (v === 'pop') {
          this.getPops()
        }
      }
    },
    methods: {
      getData() {
        this.$api.get('/settings').then(data => {
          if (data) {
            this.form = data.base
            this.ips = data.ips
            this.logs = data.logs
          }
        })
      },
      update() {
        this.$api.put('/settings', this.form).then(data => {
          if (data) {
            this.$message.success(this.$t('config_success'))
            this.getData()
          }
        })
      },
      addIp() {
        this.ips.push('')
      },
      delIp(index) {
        this.ips.splice(index, 1)
      },
      updateIp() {
        this.$api.put('/settings/ips', {ips: this.ips}).then(data => {
          if (data) {
            this.$message.success(this.$t('config_success'))
            this.getData()
          }
        })
      },
      resetData(type) {
        this.$prompt(`<span style="color: #FD4322">${this.$t('clear_warn')}</span>`, this.$t('warn'), {
          confirmButtonText: this.$t('btnYes'),
          cancelButtonText: this.$t('btnNo'),
          inputType: 'password',
          dangerouslyUseHTMLString: true,
          beforeClose(action, instance, done) {
            if (action === 'confirm') {
              this.$api.post('/datas/clean', {
                type,
                password: instance.inputValue
              }).then(data => {
                if (data) {
                  done()
                  this.$message.success(this.$t('success'))
                }
              })
            } else {
              done()
            }
          }
        }).catch(() => {})
      },
      getPops() {
        this.$api.get('/settings/pops').then(data => {
          if (data) {
            this.pops = data
          }
        })
      },
      resetForm() {
        this.show.edit = false
        this.pop = Object.assign({}, this.$options.data().pop)
      },
      edit(row) {
        let pop = {}
        for (let i in this.pop) {
          if (this.pop.hasOwnProperty(i) && row.hasOwnProperty(i)) {
            pop[i] = row[i]
          }
        }
        this.pop = pop
        this.show.update = true
        this.show.edit = true
      },
      submit() {
        if (this.pop.id > 0) {
          this.$api.put(`/settings/pops/${this.pop.id}`, this.pop).then(data => {
            if (data) {
              this.show.update = false
              this.getPops()
            }
          })
        } else {
          this.$api.post('/settings/pops', this.pop).then(data => {
            if (data) {
              this.show.update = false
              this.getPops()
            }
          })
        }
      },
      toggleEnable({id, is_enable}) {
        this.$api.patch(`/settings/pops/${id}`, {is_enable}).then(data => {
          if (data) {
            this.getPops()
          }
        })
      }
    },
    mounted() {
      this.getData()
    }
  }
</script>

<style lang="less">
  .form-price label {
    width: 95px !important;
  }

  h4.h4 {
    line-height: 30px;
    margin: 0;
    margin-bottom: 10px;
    width: 100px;
    text-align: right;

    &.mt {
      margin-top: 30px;
    }
  }

  .ip-list {
    color: #606266;
    padding-left: 100px;
    line-height: 28px;
  }

  .el-select .el-input {
    width: 86px;
    cursor: default;
  }

  .input-with-select .el-input-group__prepend {
    background-color: #fff;
  }
</style>
