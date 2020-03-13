<template>
  <div v-if="!!data">
    <span class="a" @click="getData">{{data.nickname}} ({{data.username}})</span>
    <el-dialog :title="`${v.nickname}-${$t('agent_info')}`" v-if="alive" :visible.sync="show" width="500px" height="552" @closed="alive = false">
      <div class="info">
        <div class="unit">
          <label>{{$t('username')}}：</label>
          {{v.username}}
        </div>
        <div class="unit">
          <label>{{$t('nickname')}}：</label>
          {{v.nickname}}
        </div>
        <div class="unit">
          <label>{{$t('tel')}}：</label>
          {{v.tel}}
        </div>
        <div class="unit">
          <label>{{$t('deposit_sum')}}：</label>
          {{v.deposit_sum|numFormat}}
        </div>
        <div class="unit">
          <label>{{$t('withdrawal_sum')}}：</label>
          {{v.withdrawal_sum|numFormat}}
        </div>
        <div class="unit">
          <label>{{$t('lr_sum')}}：</label>
          <span style="color: #67C23A;" v-if="Number(v.deposit_sum) - Number(v.withdrawal_sum) >= 0">
            {{Number(v.deposit_sum) - Number(v.withdrawal_sum)|numFormat}}
          </span>
          <span style="color: #ff4949;" v-else>{{Number(v.deposit_sum) - Number(v.withdrawal_sum)|numFormat}}</span>
        </div>
        <div class="unit">
          <label>{{$t('commission_rate')}}：</label>
          {{v.commission_rate|numFormat}}
        </div>
        <div class="unit">
          <label>{{$t('sr_sum')}}：</label>
          <span style="color: #67C23A;" v-if="Number(v.deposit_sum) - Number(v.withdrawal_sum) >= 0">
            {{(Number(v.deposit_sum) - Number(v.withdrawal_sum)) * v.commission_rate / 100|numFormat}}
          </span>
          <span style="color: #ff4949;" v-else>{{(Number(v.deposit_sum) - Number(v.withdrawal_sum)) * v.commission_rate / 100|numFormat}}</span>
        </div>
        <div class="unit">
          <label>{{$t('balance_sum')}}：</label>
          {{v.balance|numFormat}}
        </div>
      </div>
      <template v-if="v.logs.length > 0">
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
    methods: {
      getData() {
        this.alive = true
        this.$api.get(`/agents/${this.data.id}`).then(data => {
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