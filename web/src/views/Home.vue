<template>
  <div class="home">
    <div class="sleep" v-show="sleep">
      <h1 v-t="'trade.sleep'"></h1>
      <div class="account">
        <div class="tt" v-t="'trade.account'"></div>
        <div class="balance">{{account.balance|numFormat}}</div>
        <div class="btn">
          <button class="d" @click="showDeposit = true" v-t="'trade.deposit'"></button>
          <button @click="showWithdrawal = true" v-t="'trade.withdrawal'"></button>
        </div>
      </div>
    </div>
    <div class="right">
      <div class="account">
        <div class="tt" v-t="'trade.account'"></div>
        <div class="balance">{{account.balance|numFormat}}</div>
        <div class="btn">
          <button class="d" @click="showDeposit = true" v-t="'trade.deposit'"></button>
          <button @click="showWithdrawal = true" v-t="'trade.withdrawal'"></button>
        </div>
      </div>
      <transition-group name="list-complete" tag="div">
        <scene
            v-for="s in scenes"
            :key="s.timestamp"
            class="scene list-complete-item"
            :dt="s"
            :rate="rate"
            :prices="prices"
            :values="values[s.timestamp]"
            @trade="getData"
        ></scene>
      </transition-group>
    </div>
    <div class="left">
      <div class="kx" ref="kxt"></div>
      <div class="rate">
        <div class="tit" v-t="'trade.fx'"></div>
        <p class="th">
          <span class="t" v-t="'trade.time'"></span>
          <span v-t="'trade.open'"></span>
          <span v-t="'trade.high'"></span>
          <span v-t="'trade.low'"></span>
          <span v-t="'trade.close'"></span>
        </p>
        <div class="td">
          <p v-for="r in rates">
            <span class="t">{{r.time}}</span>
            <span>{{r.open}}</span>
            <span>{{r.high}}</span>
            <span>{{r.low}}</span>
            <span>{{r.close}}</span>
          </p>
        </div>
      </div>
      <div class="order" v-if="orders.length > 0">
        <div class="tit" v-t="'trade.jxz'"></div>
        <p class="th">
          <span class="t" v-t="'trade.sn'"></span>
          <span class="m" v-t="'trade.scene'"></span>
          <span class="r" v-t="'trade.type'"></span>
          <span v-t="'trade.amount'"></span>
          <span class="r" v-t="'trade.rate'"></span>
          <span v-t="'trade.shouyi'"></span>
          <span v-t="'trade.price'"></span>
        </p>
        <p v-for="o in orders">
          <span class="t">{{o.sn}}</span>
          <span class="m s">{{o.scene_time}}</span>
          <span :class="`type_${o.type}`" class="r">{{o.type_label}}</span>
          <span>{{o.amount}}</span>
          <span class="r">{{o.rate}}</span>
          <span>{{o.amount * o.rate}}</span>
          <span>{{o.open}}</span>
        </p>
      </div>
    </div>
    <el-dialog :title="$t('trade.deposit')" :visible.sync="showDeposit" custom-class="dialog-400" @closed="depositAmount = 0">
      <el-form label-width="80px">
        <el-form-item :label="$t('trade.depositAmount')">
          <el-input v-model="depositAmount"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="showDeposit = false" v-t="'base.btnNo'"></el-button>
        <el-button type="primary" v-t="'base.btnYes'" @click="deposit"></el-button>
      </div>
    </el-dialog>
    <el-dialog :title="$t('trade.withdrawal')" :visible.sync="showWithdrawal" custom-class="dialog-400" @closed="withdrawalAmount = 0">
      <el-form label-width="80px">
        <el-form-item :label="$t('trade.withdrawalAmount')">
          <el-input v-model="withdrawalAmount"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="showWithdrawal = false" v-t="'base.btnNo'"></el-button>
        <el-button type="primary" v-t="'base.btnYes'" @click="withdrawal"></el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
  import scene from '../components/scene'
  import {mapState} from 'vuex'
  import kx from '../kx'

  export default {
    name: 'home',
    components: {scene},
    data() {
      return {
        showDeposit: false,
        showWithdrawal: false,
        depositAmount: 0,
        withdrawalAmount: 0,
        rate: {
          1: 0,
          2: 0,
          3: 0
        },
        prices: [],
        account: {},
        orders: [],
        rates: [],
        values: {},
        pops: []
      }
    },
    watch: {
      rates: {
        handler(rates) {
          let datas = []
          rates.forEach(({time, open, close, low, high}, index) => {
            let value = [time, open, close, low, high]
            if (index === 0) {
              datas.unshift({
                value,
                itemStyle: {
                  borderWidth: 2,
                  borderColor: '#FD4322',
                  borderColor0: '#2F97E5'
                },
                emphasis: {
                  itemStyle: {
                    borderWidth: 2,
                    borderColor: '#18B387',
                    borderColor0: '#20323E'
                  },
                }
              })
            } else {
              datas.unshift(value)
            }
          })
          kx.setData(datas)
        },
        deep: true
      },
      forex: {
        handler(data) {
          if (this.rates.length === 0 || !data.time) {
            return
          }
          let same = this.rates.some((rate, index) => {
            if (data.time === rate.time) {
              this.$set(this.rates, index, data)
              return true
            }
          })

          if (!same) {
            this.getData()
          }
        },
        deep: true
      }
    },
    computed: {
      ...mapState([
        'forex',
        'scenes',
        'sleep',
        'member'
      ])
    },
    methods: {
      getData() {
        this.$api.get('/trades').then(data => {
          if (data) {
            if (this.forex.time !== '') {
              data.rates.unshift(this.forex)
            }
            this.rates = data.rates
            this.rate = data.config.rate
            this.orders = data.orders
            this.account = data.account

            if (this.prices.slice().sort().toString() !== data.config.prices.slice().sort().toString()) {
              this.prices = data.config.prices
            }

            this.values = data.values

            // 更新账号余额
            if (this.member && this.member.balance !== data.account.balance) {
              let member = Object.assign({}, this.member)
              member.balance = data.account.balance
              this.$store.commit('saveMember', member)
            }

            if (data.notifies.length > 0) {
              data.notifies.forEach(n => {
                if (Number(n.status) === 2) {
                  this.$notify({
                    title: this.$t('trade.win'),
                    type: 'success',
                    message: this.$t('trade.win_cont', [n.sn, n.amount]),
                    duration: 0
                  })
                } else {
                  this.$notify({
                    title: this.$t('trade.lose'),
                    type: 'error',
                    message: this.$t('trade.lose_cont', [n.sn]),
                    duration: 0
                  })
                }
              })
            }
          }
        })
      },
      deposit() {
        this.$api.post('/accounts/deposit', {amount: this.depositAmount}).then(data => {
          if (data) {
            this.showDeposit = false
            this.$message.success(this.$t('base.success'))
          }
        })
      },
      withdrawal() {
        this.$api.post('/accounts/withdrawal', {amount: this.withdrawalAmount}).then(data => {
          if (data) {
            this.getData()
            this.showWithdrawal = false
            this.$message.success(this.$t('base.success'))
          }
        })
      }
    },
    mounted() {
      kx.init(this.$refs.kxt)
      this.getData()
    }
  }
</script>

<style scoped lang="less">
  @import "../assets/home";

  .list-complete-item {
    transition: all 1s;
    display: block;
  }

  .list-complete-enter {
    opacity: 0;
    transform: translateY(346px);
  }

  .list-complete-leave-to {
    opacity: 0;
    transform: translateX(588px);
  }

  .list-complete-leave-active {
    position: absolute;
  }

  @media screen and (max-width: 1200px) {
    .list-complete-item {
      transition: none;
    }
  }
</style>