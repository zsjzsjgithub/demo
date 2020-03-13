<template>
  <div style="position: relative;">
    <div class="menu">
      <div class="main">
        <div class="rt">
          <el-dropdown class="account" trigger="click"  placement="bottom" @command="doCmd">
            <span class="account-link">
              {{member ? (member.nickname ? member.nickname : member.username) : ''}}<i class="el-icon-caret-bottom el-icon--right"></i>
            </span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item v-t="'change_password'" command="showPwd"></el-dropdown-item>
              <el-dropdown-item command="sprofile" v-if="isAdmin" v-t="'profile'"></el-dropdown-item>
              <el-dropdown-item divided class="logout" v-t="'logout'" command="loginout"></el-dropdown-item>
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
        <div class="logo">FX HOS</div>
        <div class="menu-list" v-if="isAdmin">
          <span class="in" :class="{s: topData.deposit.pending > 0}" @click="finance('deposit' ,'pending', '1', '1')">{{$t('deposit_pending')}}<em>{{topData.deposit.pending}}</em></span>
          <span class="in" :class="{s: topData.deposit.waitting > 0}" @click="finance('deposit' ,'waitting', '1', '4')">{{$t('deposit_waitting')}}<em>{{topData.deposit.waitting}}</em></span>
          <span class="in r" :class="{s: topData.deposit.completed > 0}" @click="finance('deposit' ,'completed', '1', '3')">{{$t('deposit_completed')}}<em>{{topData.deposit.completed}}</em></span>
          <span class="out" :class="{s: topData.withdrawal.pending > 0}" @click="finance('withdrawal' ,'pending', '2', '1')">{{$t('withdrawal_pending')}}<em>{{topData.withdrawal.pending}}</em></span>
          <span class="out" :class="{s: topData.withdrawal.waitting > 0}" @click="finance('withdrawal' ,'waitting', '2', '4')">{{$t('withdrawal_waitting')}}<em>{{topData.withdrawal.waitting}}</em></span>
          <span class="out r" :class="{s: topData.withdrawal.completed > 0}" @click="finance('withdrawal' ,'completed', '2', '3')">{{$t('withdrawal_completed')}}<em>{{topData.withdrawal.completed}}</em></span>
          <span :class="{s: topData.question > 0}" @click="question">{{$t('member_question')}}<em>{{ topData.question}}</em></span>
          <span :class="{s: topData.member.new > 0}" @click="mem('new')">{{$t('member_new')}}<em>{{topData.member.new}}</em></span>
          <span :class="{s: topData.member.online > 0}" @click="mem('online')">{{$t('member_online')}}<em>{{topData.member.online}}</em></span>
        </div>
      </div>
    </div>
    <el-dialog :title="$t('change_password')" :visible.sync="showPassword" width="400px">
      <el-form label-width="80px" :model="form" :rules="rules">
        <el-form-item :label="$t('old_password')" prop="old_password">
          <el-input v-model="form.old_password" show-password></el-input>
        </el-form-item>
        <el-form-item :label="$t('new_password')" prop="password">
          <el-input v-model="form.password" show-password></el-input>
        </el-form-item>
        <el-form-item :label="$t('password_confirmation')" prop="password_confirmation">
          <el-input v-model="form.password_confirmation" show-password></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="showPassword = false" v-t="'btnNo'"></el-button>
        <el-button type="primary" v-t="'btnYes'" @click="changePwd"></el-button>
      </div>
    </el-dialog>
    <el-dialog title="个人资料" :visible.sync="showProfile" width="400px" v-if="isAdmin">
      <el-form label-width="80px" :model="form" :rules="rules">
        <el-form-item label="账号">
          <el-input v-model="profile.username"></el-input>
        </el-form-item>
        <el-form-item label="姓名">
          <el-input v-model="profile.nickname"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="showProfile = false" v-t="'btnNo'"></el-button>
        <el-button type="primary" v-t="'btnYes'" @click="changeProfile"></el-button>
      </div>
    </el-dialog>
    <div class="sound" v-if="isAdmin">
      <audio autoplay loop :src="`${soundPath}sound1.wav`" v-if="topData.deposit.pending > 0"></audio>
      <audio autoplay loop :src="`${soundPath}sound2.m4r`" v-if="topData.withdrawal.pending > 0"></audio>
      <audio autoplay loop :src="`${soundPath}sound3.m4r`" v-if="topData.question > 0"></audio>
      <audio autoplay :src="`${soundPath}sound4.m4r`" v-if="show.new"></audio>
    </div>
  </div>
</template>

<script>
  import {mapState, mapActions} from 'vuex'
  import ws from '../../ws'

  export default {
    data() {
      return {
        showPassword: false,
        showProfile: false,
        rules: {
          old_password: {required: true, message: this.$t('required', [this.$t('old_password')])},
          password: {required: true, message: this.$t('required', [this.$t('new_password')])},
          password_confirmation: {required: true, message: this.$t('required', [this.$t('password_confirmation')])},
        },
        form: {
          old_password: '',
          password: '',
          password_confirmation: ''
        },
        profile: {
          username: '',
          nickname: ''
        },
        show: {
          new: false
        },
        soundPath: process.env.BASE_URL + 'sound/',
        isFirst: true
      }
    },
    computed: {
      ...mapState([
        'lang',
        'member',
        'topData',
        'isAdmin'
      ])
    },
    watch: {
      topData(v, old) {
        if (!this.isFirst && v.member.new > old.member.new) {
          this.show.new = false
          this.$nextTick(() => {
            this.show.new = true
          })
        }
        this.isFirst = false
      }
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
      sprofile() {
        this.showProfile = true
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
            this.$message.success(this.$t('change_pwd'))
            this.loginout()
          }
        })
      },
      changeProfile() {
        this.$api.put('/tokens/profile', this.profile).then(data => {
          if (data) {
            this.loginout()
          }
        })
      },
      doCmd(cmd) {
        this[cmd]()
      },
      finance(parent, key, type, status) {
        if (this.topData[parent][key] > 0) {
          if (this.$route.name !== 'finance') {
            this.$router.push({name: 'finance'})
          }
          let date = []
          if (key === 'completed') {
            let today = moment().utcOffset(TZ).startOf('day').format()
            date = [today, today]
          }
          this.$store.commit('setFinance', {type, status, date})
        }
      },
      question() {
        if (this.topData.question > 0) {
          if (this.$route.name !== 'message') {
            this.$router.push({name: 'message'})
          }
          this.$store.commit('setQuestion', true)
        }
      },
      mem(key) {
        if (this.topData.member[key] > 0) {
          if (this.$route.name !== 'home') {
            this.$router.push({name: 'home'})
          }
          let data = {
            online: false,
            date: []
          }
          if (key === 'new') {
            let today = moment().utcOffset(TZ).startOf('day').format()
            data.date = [today, today]
          } else {
            data.online = true
          }
          this.$store.commit('setMem', data)
        }
      }
    },
    mounted() {
      if (this.isAdmin) {
        ws.init()
      }
      this.getMemberInfo().then(() => {
        this.profile.username = this.member.username
        this.profile.nickname = this.member.nickname
      })
    },
    beforeDestroy() {
      if (this.isAdmin) {
        ws.close()
      }
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

      a {

        &:hover {
          text-decoration: underline;
        }
        em {
          font-style: normal;
          color: #FD4322;
        }
      }
    }
  }

  .menu {
    background: @color_blue;
    color: #fff;
    line-height: 64px;

    .main {
      overflow: auto;
      padding: 0 20px;

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
        margin-right: 220-164px;
      }

      .menu-list {
        height: 64px;
        overflow: hidden;
        .fl;

        span {
          .fl;
          margin-top: 22px;
          font-size: 12px;
          height: 20px;
          line-height: 20px;
          border-radius: 3px;
          background-color: #fff;
          color: #707377;
          padding-left: 6px;
          margin-right: 8px;
          overflow: hidden;

          &.r {
            margin-right: 32px;
          }

          em {
            .fr;
            height: 20px;
            padding-left: 6px;
            font-style: normal;
            margin-left: 4px;
            padding-right: 6px;
            background: #bababa;
            color: #fafafa;
          }


          &.s {
            cursor: pointer;
            background: #fff;

            em {
              background: #79bbef;
            }

            &.in em {
              background: #67c23a;
            }

            &.out em {
              background: #F56C6C;
            }
          }
        }
      }
    }
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

  .sound {
    display: none;
  }
</style>
