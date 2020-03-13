<template>
  <div class="login">
    <div class="form">
      <el-dropdown class="lang" trigger="click" placement="bottom" @command="submitLang">
            <span class="lang-link">
              <template v-if="lang === 'ko_KR'">
                <em class="ko_KR"></em>한국어
              </template>
              <template v-if="lang === 'zh_CN'">
                <em></em>中文
              </template>
              <i class="el-icon-caret-bottom el-icon--right"></i>
            </span>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item command="ko_KR" v-show="lang !== 'ko_KR'">한국어</el-dropdown-item>
          <el-dropdown-item command="zh_CN" v-show="lang !== 'zh_CN'">中文</el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
      <div class="logo"><img src="../assets/logo.jpg" alt=""></div>
      <el-form ref="login" :model="form" :rules="rules" @submit.native.prevent="submit">
        <el-form-item :label="$t('field.username')" prop="username">
          <el-input v-model="form.username"></el-input>
        </el-form-item>
        <el-form-item :label="$t('field.password')" prop="password">
          <el-input v-model="form.password" show-password></el-input>
        </el-form-item>
        <el-form-item align="right">
          <p v-t="'login.tip'"></p>
          <el-button type="text" v-t="'base.reg'" @click="showReg = true" v-if="allow"></el-button>
          <el-button type="primary" v-t="'base.login'" native-type="submit"></el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-dialog :title="$t('base.reg')"  v-if="allow" :visible.sync="showReg" custom-class="dialog-500" :close-on-click-modal="false" :close-on-press-escape="false" :show-close="false">
      <el-form label-width="80px" ref="reg" :model="regForm" :rules="regRules" @submit.native.prevent="reg">
        <el-form-item :label="$t('field.username')" prop="username">
          <el-input v-model="regForm.username" @change="check_username = false">
            <span slot="append" v-if="check_username" v-t="'login.successUser'" class="success"></span>
            <el-button slot="append" v-else v-t="'base.check'" @click="check('username')"></el-button>
          </el-input>
        </el-form-item>
        <el-form-item :label="$t('field.password')" prop="password">
          <el-input v-model="regForm.password" show-password></el-input>
        </el-form-item>
        <el-form-item :label="$t('field.password_confirmation')" prop="password_confirmation">
          <el-input v-model="regForm.password_confirmation" show-password></el-input>
        </el-form-item>
        <el-form-item :label="$t('field.tel')" prop="tel">
          <el-input v-model="regForm.tel"></el-input>
        </el-form-item>
        <el-form-item :label="$t('field.bank_name')" prop="bank_name">
          <el-input v-model="regForm.bank_name"></el-input>
        </el-form-item>
        <el-form-item :label="$t('field.bank_number')" prop="bank_number">
          <el-input v-model="regForm.bank_number"></el-input>
        </el-form-item>
        <el-form-item :label="$t('field.nickname')" prop="nickname">
          <el-input v-model="regForm.nickname"></el-input>
        </el-form-item>
        <el-form-item :label="$t('field.agent_name')" prop="agent_name">
          <el-input v-model="regForm.agent_name" @change="check_agent_name = false">
            <span slot="append" v-if="check_agent_name" v-t="'login.successAgent'" class="success"></span>
            <el-button slot="append" v-else v-t="'base.check'" @click="check('agent_name')"></el-button>
          </el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="showReg = false" v-t="'base.btnNo'"></el-button>
        <el-button type="primary" v-t="'base.btnYes'" native-type="submit" @click="reg"></el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
  import {mapState,mapActions} from 'vuex'

  export default {
    name: 'login',
    data() {
      return {
        form: {
          username: '',
          password: '',
          type: 3
        },
        rules: {
          username: {required: true, message: this.$t('check.required', [this.$t('field.username')])},
          password: {required: true, message: this.$t('check.required', [this.$t('field.password')])}
        },
        showReg: false,
        check_username: false,
        check_agent_name: false,
        regForm: {
          nickname: '',
          username: '',
          password: '',
          password_confirmation: '',
          tel: '',
          bank_name: '',
          bank_number: '',
          agent_name: ''
        },
        regRules: {
          nickname: {required: true, message: this.$t('check.required', [this.$t('field.nickname')])},
          username: {required: true, message: this.$t('check.required', [this.$t('field.username')])},
          password: {required: true, message: this.$t('check.required', [this.$t('field.password')])},
          password_confirmation: {required: true, message: this.$t('check.required', [this.$t('field.password_confirmation')])},
          tel: {required: true, message: this.$t('check.required', [this.$t('field.tel')])},
          bank_name: {required: true, message: this.$t('check.required', [this.$t('field.bank_name')])},
          bank_number: {required: true, message: this.$t('check.required', [this.$t('field.bank_number')])},
          agent_name: {required: true, message: this.$t('check.required', [this.$t('field.agent_name')])}
        },
        allow: false
      }
    },
    computed: mapState(['lang']),
    methods: {
      ...mapActions([
        'login',
        'register',
        'selLang'
      ]),
      submitLang(lang) {
        this.selLang(lang)
        global.location.reload()
      },
      submit() {
        this.$refs.login.validate(r => {
          if (r) {
            this.login(this.form).then(() => {
              this.$router.push('/')
            })
          }
        })
      },
      reg() {
        this.$refs.reg.validate(r => {
          if (r) {
            this.register(this.regForm).then(() => {
              this.$router.push('/')
            })
          }
        })
      },
      check(key) {
        this.$api.get('/tokens/check', {params: {[key]: this.regForm[key]}}).then(data => {
          if (data && data.check) {
            this[`check_${key}`] = true
          }
        })
      },
      allows() {
        this.$api.get('/allow').then(data => {
          if (data) {
            this.allow = Boolean(data.allow_register)
          }
        })
      }
    },
    mounted() {
      this.allows()
    }
  }
</script>

<style scoped lang="less">
  @import "../assets/config";

  .login {
    /*background: #000;*/

    .form {
      position: fixed;
      left: 0;
      right: 0;
      bottom: 0;
      top: 0;
      margin: auto;
      width: 430px;
      height: 500px;

      .logo {
        .tc;
        clear: both;
        height: 150px;
        background: #eee;
        line-height: 150px;
        font-size: 46px;
        margin-bottom: 16px;
      }

      p {
        .fl;
        font-size: 12px;
        color: #333;
      }
    }
    
    .success {
      color: #67C23A;
    }
  }
  .lang {
    border-radius: 4px;
    cursor: pointer;
    margin-bottom: 8px;
    float: right;

    &:hover {
      .hover;
    }

    &-link {
      display: block;
      height: 20px;
      line-height: 20px;
      font-size: 12px;
      /*color: #fff;*/
      /*padding: 10px;*/

      .el-icon-caret-bottom {
        font-size: 12px;
      }

      em {
        .fl;
        width: 30px;
        height: 20px;
        background: url("../assets/lang.jpg") no-repeat;
        margin-right: 6px;

        &.ko_KR {
          background-position-y: -20px;
        }
      }
    }
  }

</style>
