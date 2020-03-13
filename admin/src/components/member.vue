<template>
  <div v-if="!!data">
    <span class="a" v-if="isAdmin" @click="getData">{{data.nickname}} ({{data.username}})</span>
    <span v-else>{{data.nickname}} ({{data.username}})</span>
    <el-dialog :title="`${v.nickname}-${$t('member_info')}`" v-if="alive" :visible.sync="show" width="500px" height="552" @closed="alive = false">
      <div class="info">
        <div class="unit">
          <label>{{$t('username')}}：</label>
          {{v.username}}
        </div>
        <div class="unit">
          <label>{{$t('tel')}}：</label>
          {{v.tel}}
        </div>
        <div class="unit">
          <label>{{$t('bank_name')}}：</label>
          {{v.bank_name}}
        </div>
        <div class="unit">
          <label>{{$t('bank_number')}}：</label>
          {{v.bank_number}}
        </div>
        <div class="unit">
          <label>{{$t('nickname')}}：</label>
          {{v.nickname}}
        </div>
        <div class="unit" v-if="!!v.agent">
          <label>{{$t('menu_agent')}}：</label>
          {{v.agent.nickname}} ({{v.agent.username}})
        </div>
        <div class="unit">
          <label>{{$t('deposit')}}：</label>
          {{v.deposit|numFormat}}
        </div>
        <div class="unit">
          <label>{{$t('withdrawal')}}：</label>
          {{v.withdrawal|numFormat}}
        </div>
        <div class="unit">
          <label>{{$t('balance')}}：</label>
          {{v.balance|numFormat}}
        </div>
      </div>
      <template v-if="isAdmin && v.logs.length > 0">
        <hr>
        <div class="login-list">
          <h5>{{$t('login_log')}}：</h5>
          <div class="list">
            <p v-for="l in v.logs" :key="l.id">{{l.time}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{l.ip}}</p>
          </div>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script>
  import {mapState} from 'vuex'

  export default {
    name: 'member',
    props: {
      data: Object
    },
    data() {
      return {
        show: false,
        alive: false,
        v: {
          logs: []
        }
      }
    },
    computed: mapState(['isAdmin']),
    methods: {
      getData() {
        this.alive = true
        this.$api.get(`/members/${this.data.id}`).then(data => {
          if (data) {
            this.v = data
            this.show = true
          }
        })
      }
    }
  }
</script>

<style scoped lang="less">
  @import "../assets/config";

  .info {
    color: #606266;

    .unit {
      line-height: 20px;
      margin-bottom: 16px;
      overflow: auto;

      label {
        .fl;
        width: 120px;
        text-align: right;
        margin-right: 12px;
        color: #99a9bf;
      }
    }
  }

  hr {
    border: 2px dashed #eee;
    margin: 0;
    padding: 0;
  }

  .login-list {
    color: #606266;

    h5 {
      .fl;
      width: 120px;
      text-align: right;
      line-height: 40px;
      margin: 0;
      color: #99a9bf;
    }

    .list {
      padding-top: 10px;

      p {
        padding-left: 132px;
        margin: 0;
        font-size: 12px;
        line-height: 20px;
      }
    }

  }
</style>