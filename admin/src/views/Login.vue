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
      <el-form ref="login" :model="form" :rules="rules" @submit.native.prevent="submit">
        <el-form-item :label="$t('username')" prop="username">
          <el-input v-model="form.username"></el-input>
        </el-form-item>
        <el-form-item :label="$t('password')" prop="password">
          <el-input v-model="form.password" show-password></el-input>
        </el-form-item>
        <el-form-item align="right">
          <el-button type="primary" v-t="'login'" native-type="submit"></el-button>
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script>
  import {mapActions, mapState} from 'vuex'

  export default {
    name: 'login',
    data() {
      return {
        form: {
          username: '',
          password: ''
        },
        rules: {
          username: {required: true, message: this.$t('required', [this.$t('username')])},
          password: {required: true, message: this.$t('required', [this.$t('password')])}
        }
      }
    },
    computed: {
      ...mapState(['lang'])
    },
    methods: {
      ...mapActions([
        'login',
        'selLang'
      ]),
      submit() {
        this.$refs.login.validate(r => {
          if (r) {
            this.login(this.form).then(() => {
              this.$router.push('/')
            })
          }
        })
      },
      submitLang(lang) {
        this.selLang(lang)
        global.location.reload()
      },
    }
  }
</script>

<style scoped lang="less">
  @import "../assets/config";

  .login {
    .form {
      position: fixed;
      left: 0;
      right: 0;
      bottom: 0;
      top: 0;
      margin: auto;
      width: 430px;
      height: 500px;

      h4 {
        font-size: 24px;
        line-height: 40px;
        text-align: center;
        margin-bottom: 20px;
        color: @color_deep;
      }

      .logo {
        .tc;
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
  }

  .lang {
    //.fr;
    overflow: auto;
    display: block;
    width: 120px;
    margin-left: 310px;
    margin-top: (64-40)/2px;
    border-radius: 4px;
    cursor: pointer;

    &:hover {
      .hover;
    }

    &-link {
      display: block;
      height: 20px;
      line-height: 20px;
      font-size: 12px;
      /*color: #fff;*/
      padding: 10px;

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
