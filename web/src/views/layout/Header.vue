<template>
  <div>
    <div class="top-bar" v-show="!sleep">
      <div class="top-bar-main">
        <!--<el-dropdown trigger="click" placement="bottom">-->
          <!--<span class="dropdown-link">-->
            <!--GBP/AUD-->
            <!--<i class="el-icon-caret-bottom el-icon&#45;&#45;right"></i>-->
          <!--</span>-->
          <!--<el-dropdown-menu slot="dropdown">-->
            <!--<el-dropdown-item disabled>GBP/AUD</el-dropdown-item>-->
          <!--</el-dropdown-menu>-->
        <!--</el-dropdown>-->
        <span class="dropdown-link">GBP/AUD</span>
        &nbsp;
        {{time}}
        &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="rrt">
          O&nbsp;<em>{{forex.open}}</em>
          &nbsp;&nbsp;&nbsp;
          H&nbsp;<em>{{forex.high}}</em>
          &nbsp;&nbsp;&nbsp;
          L&nbsp;<em>{{forex.low}}</em>
          &nbsp;&nbsp;&nbsp;
          C&nbsp;<em>{{forex.close}}</em>
        </span>
      </div>
    </div>
    <div class="menu">
      <div class="main">
        <div class="rt">
          <el-dropdown class="account" trigger="click"  placement="bottom" @command="doCmd">
            <span class="account-link">
              {{member ? (member.nickname ? member.nickname : member.username) : ''}}<i class="el-icon-caret-bottom el-icon--right"></i>
            </span>
            <el-dropdown-menu slot="dropdown">
              <p class="ye"><label v-t="'header.balance'">:</label> {{(member ? member.balance: 0)|numFormat}}</p>
              <el-dropdown-item v-t="'base.change_password'" command="showPwd"></el-dropdown-item>
              <el-dropdown-item divided class="logout" v-t="'base.logout'" command="loginout"></el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
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
        </div>
        <router-link to="/" class="logo">FX HOS</router-link>
        <el-dropdown class="menu-list-down" trigger="click" placement="bottom">
          <span class="title">
            <span v-if="$route.name === 'home'" v-t="'header.trade'"></span>
            <span v-else-if="$route.name === 'rate'" v-t="'header.rate'"></span>
            <span v-else-if="$route.name === 'order'" v-t="'header.order'"></span>
            <span v-else-if="$route.name === 'account'" v-t="'header.account'"></span>
            <span v-else-if="$route.name === 'chat'" v-t="'header.chat'"></span>
            <span v-else v-t="'header.message'"></span>
            <i class="el-icon-arrow-down el-icon--right"></i>
          </span>
          <el-dropdown-menu slot="dropdown">
            <el-dropdown-item><router-link class="adown" to="/" v-t="'header.trade'"></router-link></el-dropdown-item>
            <el-dropdown-item><router-link class="adown" to="/rate" v-t="'header.rate'"></router-link></el-dropdown-item>
            <el-dropdown-item><router-link class="adown" to="/order" v-t="'header.order'"></router-link></el-dropdown-item>
            <el-dropdown-item><router-link class="adown" to="/account" v-t="'header.account'"></router-link></el-dropdown-item>
            <el-dropdown-item><router-link class="adown" to="/message" v-t="'header.message'"></router-link></el-dropdown-item>
            <el-dropdown-item><router-link class="adown" to="/chat" v-t="'header.chat'"></router-link></el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
        <div class="menu-list">
          <router-link to="/" v-t="'header.trade'"></router-link>
          <router-link to="/rate" v-t="'header.rate'"></router-link>
          <router-link to="/order" v-t="'header.order'"></router-link>
          <router-link to="/account" v-t="'header.account'"></router-link>
          <router-link to="/message" :class="{sel: ['service_detail', 'problem', 'notice'].includes($route.name)}" v-t="'header.message'"></router-link>
          <router-link to="/chat" v-t="'header.chat'"></router-link>
        </div>
      </div>
    </div>
    <el-dialog :title="$t('base.change_password')" :visible.sync="showPassword" custom-class="dialog-400">
      <el-form label-width="80px" :model="form" :rules="rules">
        <el-form-item :label="$t('field.old_password')" prop="old_password">
          <el-input v-model="form.old_password" show-password></el-input>
        </el-form-item>
        <el-form-item :label="$t('field.new_password')" prop="password">
          <el-input v-model="form.password" show-password></el-input>
        </el-form-item>
        <el-form-item :label="$t('field.password_confirmation')" prop="password_confirmation">
          <el-input v-model="form.password_confirmation" show-password></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="showPassword = false" v-t="'base.btnNo'"></el-button>
        <el-button type="primary" v-t="'base.btnYes'" @click="changePwd"></el-button>
      </div>
    </el-dialog>
    <el-card v-for="(p, i) in pops" :key="p.id" class="popcard" :style="`
      width: ${p.width}px;
      ${p.xt === 'center' ? `left: 50%; margin-left: -${p.width/2+1}px` : `${p.xt}: ${p.x}px`};
      ${p.yt === 'center' ? `top: 50%; margin-top: -${p.height/2+1}px` : `${p.yt}: ${p.y}px`};
    `">
      <div slot="header" class="header">
        <span>{{p.title}}</span>
        <i class="el-icon-close" @click="pops.splice(i, 1)"></i>
      </div>
      <div v-html="p.content" :style="{height: `${p.height - 57}px`}" class="body"></div>
    </el-card>
  </div>
</template>

<script>
  import ws from './../../ws'
  import {mapState, mapActions} from 'vuex'

  export default {
    data() {
      return {
        showPassword: false,
        rules: {
          old_password: {required: true, message: this.$t('check.required', [this.$t('field.old_password')])},
          password: {required: true, message: this.$t('check.required', [this.$t('field.new_password')])},
          password_confirmation: {required: true, message: this.$t('check.required', [this.$t('field.password_confirmation')])},
        },
        form: {
          old_password: '',
          password: '',
          password_confirmation: ''
        },
        intl: null,
        pops: []
      }
    },
    computed: {
      ...mapState([
        'lang',
        'member',
        'forex',
        'time',
        'sleep'
      ])
    },
    methods: {
      ...mapActions([
        'selLang',
        'logout',
        'getMemberInfo',
      ]),
      submitLang(lang) {
        this.selLang(lang)
        global.location.reload()
      },
      showPwd() {
        this.showPassword = true
      },
      loginout() {
        this.logout().then(() => {
          global.location.reload()
        })
      },
      changePwd() {
        this.$api.put('/tokens/password', this.form).then(data => {
          if (data) {
            this.showPassword = false
            this.$message.success(this.$t('head.change_pwd'))
            this.loginout()
          }
        })
      },
      doCmd(cmd) {
        this[cmd]()
      },
      getPops() {
        this.$api.get('/settings/pops?is_enable=1').then(data => {
          if (data) {
            this.pops = data
          }
        })
      }
    },
    mounted() {
      this.getPops()
      this.getMemberInfo()
      ws.init()
    },
    beforeDestroy() {
      ws.close()
    }
  }
</script>

<style scoped lang="less">
  @import "../../assets/config";

  .top-bar {
    background: @color_deep;

    &-main {
      .wm;
      line-height: 46px;
      height: 46px;
      overflow: hidden;
      color: #fff;
      font-size: 12px;

      .dropdown-link {
        color: #10ADE4;
        cursor: pointer;
        font-size: 12px;
      }

      .el-icon-caret-bottom {
        font-size: 12px;
      }

      em {
        font-style: normal;
        color: #FD4322;
      }
    }
  }

  .menu {
    background: @color_blue;
    color: #fff;
    line-height: 64px;

    .main {
      .wm;
      overflow: auto;

      .rt {
        .fr;

        .account {
          .fl;
          margin-top: (64-40)/2px;
          border-radius: 4px;
          cursor: pointer;

          &:hover {
            .hover;
          }

          &-link {
            line-height: 40px;
            height: 40px;
            display: block;
            font-size: 16px;
            color: #fff;
            padding: 0 10px;

            .el-icon-caret-bottom {
              font-size: 14px;
            }
          }
        }

        .lang {
          .fl;
          margin-left: 10px;
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
            color: #fff;
            padding: 10px;

            .el-icon-caret-bottom {
              font-size: 12px;
            }

            em {
              .fl;
              width: 30px;
              height: 20px;
              background: url("../../assets/lang.jpg") no-repeat;
              margin-right: 6px;

              &.ko_KR {
                background-position-y: -20px;
              }
            }
          }
        }
      }

      .logo {
        height: 64px;
        line-height: 70px;
        .fl;
        width: 164px;
        font-size: 36px;
        font-weight: bold;
      }

      .menu-list {
        .fl;
        a {
          .fl;
          .tc;
          font-size: 16px;
          width: 100px;

          &:hover {
            .hover;
          }

          &.router-link-exact-active, &.sel {
            .hover;
          }
        }
      }

      .menu-list-down {
        .fl;
        .hover;
        text-align: center;
        color: #fff;
        width: 110px;
        height: 40px;
        line-height: 40px;
        display: none;
        span.title {
          font-size: 16px;
        }
      }
    }
  }

  .adown {
    display: block;
  }

  .logout {
    color: #F56C6C;
  }

  .ye {
    background: #f9f9f9;
    line-height: 36px;
    padding: 0 20px;
    color: #606266;
    label {
      color: #FF8A1E;
    }
  }

  @media screen and (max-width: 1200px) {
    .top-bar {
      &-main {
        padding: 8px 0;
        padding-left: 10px;
        width: 100%;
        box-sizing: border-box;
        line-height: 20px;
        height: auto;

        .rrt {
          display: inline-block;
        }
      }
    }

    .menu {
      .main {
        box-sizing: border-box;
        height: 40px;
        line-height: 40px;
        width: 100%;
        padding: 0 10px;
        .logo {
          display: none;
        }

        .menu-list {
          display: none;
        }

        .menu-list-down {
          display: block;
        }

        .rt .account, .rt .lang {
          margin-top: 0;
        }
      }
    }
  }

  @media screen and (max-width: 500px) {
    .top-bar {
      &-main {
        .rrt {
          display: none;
        }
      }
    }
  }
</style>

<style lang="less">
  @import "../../assets/config";
  .popcard {
    position: fixed;
    z-index: 200;

    .el-card__header, .el-card__body {
      padding: 9px 10px;
    }

    .header {
      .el-icon-close {
        .fr;
        font-size: 16px;
        color: #909399;
        cursor: pointer;

        &:hover {
          color: #409EFF;
        }
      }
    }

    .body {
      overflow: auto;
    }

  }
</style>