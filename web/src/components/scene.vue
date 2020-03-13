<template>
  <div>
    <div class="scene-title">
      <span class="status" :class="[`st-${status}`, `tp-${dt.type}`]">{{info}}</span>
      {{$t('trade.scene')}}: <b>{{dt.time}}</b>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <template v-if="dt.price !== ''">
        {{$t('trade.price')}}：<b>{{dt.price}}</b>
      </template>
    </div>
    <div class="rt" :class="t.label" v-for="t in types" :key="t.id">
      <div class="bar">
        {{t.label}}
        <small>(×{{rate[t.id]}})</small>
      </div>
      <div class="jy">
        <rate-item :disabled="totalAmount.type !== 0 && totalAmount.type !== t.id" :readonly="status !== 1 || values.length > 0" :isMax="totalAmount.number >= 10" :type="t.id" v-for="(a, i) in amounts[t.id]" :key="i" v-model="amounts[t.id][i]"></rate-item>
      </div>
    </div>
    <div class="scene-bot">
      <span>{{$t('base.total')}}: <b>{{totalAmount.price|numFormat}}</b></span>
      <button class="can" v-if="status === 1 && values.length === 0" @click="init" v-t="'base.reset'"></button>
      <button v-if="status === 1 && values.length === 0" @click="submit" v-t="'base.btnYes'"></button>
      <span class="txt" v-if="status === 2 && values.length === 0">{{$t('trade.one')}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
      <span class="txt" v-if="status !== 3 && values.length > 0">{{$t('trade.wait')}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
      <span class="txt" v-if="status === 3 && dt.type !== 0">{{$t('trade.result')}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    </div>
  </div>
</template>

<script>
  import rateItem from './rateItem'

  export default {
    name: 'scene',
    components: {
      rateItem
    },
    props: {
      // 当前场次数据
      dt: {
        type: Object,
        default: {
          long: '',
          status: 1,
          time: '',
          timestamp: 0,
          price: '',
          type: 0
        }
      },
      prices: {
        type: Array,
        default: () => {
          return []
        }
      },
      rate: {
        type: Object,
        default: () => {
          return {
            1: 0,
            2: 0,
            3: 0
          }
        }
      },
      // 数据格式：[{type: 1, price: 5000, count: 1}]
      values: {
        type: Array,
        default: () => {
          return []
        }
      },
    },
    data() {
      return {
        types: [
          {
            id: 1,
            label: 'BUY'
          },
          {
            id: 2,
            label: 'SELL'
          },
          {
            id: 3,
            label: 'CANCEL'
          }
        ],
        amounts: {
          1: [],
          2: [],
          3: []
        }
      }
    },
    computed: {
      info() {
        if (this.dt) {
          if (this.dt.status === 3) {
            switch (this.dt.type) {
              case 1:
                return 'BUY'

              case 2:
                return 'SELL'

              case 3:
                return 'CANCEL'

              default:
                return this.$t('trade.jxz')
            }
          }
          return this.dt.long
        }
        return ''
      },
      status() {
        return this.dt.status
      },
      totalAmount() {
        let amount = {
          type: 0,
          price: 0,
          number: 0
        };
        for (let type in this.amounts) {
          if (this.amounts.hasOwnProperty(type) && this.amounts[type].length > 0) {
            this.amounts[type].forEach(({price, number}) => {
              if (number > 0) {
                amount.type = Number(type)
              }
              amount.price += price * number
              amount.number += number
            })
          }
        }
        return amount
      },
      initAmounts() {
        let types = [1, 2, 3]
        let amounts = {
          1: [],
          2: [],
          3: []
        }
        let prices = this.prices
        let values = this.values
        types.forEach(type => {
          if (prices.length > 0) {
            prices.forEach(price => {
              let number = 0
              if (values.length > 0) {
                values.some(value => {
                  if (Number(value.type) === Number(type) && Number(value.price) === Number(price)) {
                    number = value.count
                    return true
                  }
                })
              }
              amounts[type].push({price, number})
            })
          }
        })
        return amounts
      }
    },
    watch: {
      status(v, old) {
        // 如果不开放，则恢复初始数据
        if (old === 1 && v === 2) {
          this.init()
        }
      },
      initAmounts() {
        this.init()
      }
    },
    methods: {
      init() {
        this.amounts = JSON.parse(JSON.stringify(this.initAmounts))
      },
      submit() {
        let data = {
          scene_time: this.dt.time,
          type: 0,
          list: []
        }

        for (let type in this.amounts) {
          if (this.amounts.hasOwnProperty(type) && this.amounts[type].length > 0) {
            this.amounts[type].forEach(({price, number}) => {
              if (number > 0) {
                data.type = type
                data.list.push({price, count: number})
              }
            })
          }
        }

        if (data.type === 0 || data.list.length === 0) {
          this.$message.error(this.$t('trade.buy_error'))
          return
        }

        this.$api.post('/trades', data).then(data => {
          if (data) {
            this.$emit('trade')
          }
        })
      }
    },
    mounted() {
      this.init()
    }
  }
</script>

<style scoped lang="less">
  @import "../assets/config";

  .scene-title {
    line-height: 50px;
    font-size: 16px;
    padding: 0 20px;
    border: 1px solid #ccc;
    border-bottom: none;
    color: #666;

    b {
      color: #4A4A4A;
      font-weight: normal;
    }

    .status {
      .tc;
      .fr;
      margin-top: (50-26)/2px;
      box-sizing: border-box;
      height: 26px;
      line-height: 24px;
      border: 1px solid #FF8A1E;
      color: #FF8A1E;
      padding: 0 8px;

      &.st-2 {
        border-color: #ccc;
        color: #ccc;
      }

      &.st-3 {
        border-color: @color_sell;
        color: @color_sell;

        &.tp-1 {
          border-color: @color_buy;
          color: @color_buy;
        }
        &.tp-3 {
          border-color: @color_cancel;
          color: @color_cancel;
        }
      }
    }
  }

  .rt {
    border: 1px solid #ccc;
    border-bottom: none;
    overflow: auto;
    
    .bar {
      .fl;
      .tc;
      width: 74px;
      font-weight: bold;
      line-height: 18px;
      padding: (80-36)/2px 0;

      small {
        display: block;
        font-size: 12px;
        font-weight: normal;
      }
    }

    .jy {
      .fl;
      display: flex;
      width: 586-74px;

      > div {
        flex: 1;
      }

      .rate-item {
        padding: (80-52)/2px 0;
      }
    }
    
    &.BUY {
      .bar {
        background: #FCE2DD;
        color: #FD4322;
      }
    }

    &.SELL {
      .bar {
        background: #D9EEFD;
        color: #2F97E5;
      }
    }

    &.CANCEL {
      .bar {
        background: #D9F6EE;
        color: #18B387;
      }
    }
  }

  .scene-bot {
    line-height: 48px;
    border: 1px solid #ccc;
    display: flex;

    > *{
      flex: 2;
    }

    span {
      color: #666;
      font-size: 14px;
      text-indent: 20px;

      b{
        font-size: 20px;
        color: #20323E;
      }

      &.txt {
        font-size: 14px;
        color: #666;
        text-align: right;
      }
    }

    button {
      flex: 1;
      margin: 0;
      padding: 0;
      cursor: pointer;
      background: @color_deep;
      border: none;
      font-weight: bold;
      font-size: 20px;
      color: #fff;

      &.can {
        background: #aaa;
      }
    }
  }

  .disscene {

  }

  @media screen and (max-width: 1200px) {
    .scene-title {
      font-size: 12px;
      line-height: 30px;
      padding: 0 6px;
      .status {
        margin-top: (30-16)/2px;
        height: 16px;
        line-height: 14px;
      }
    }

    .rt {

      &.SELL, &.CANCEL {
        border-top: none;
      }

      .bar {
        width: 50px;
        font-size: 12px;
        line-height: 14px;
        padding: (36-28)/2px 0;
      }

      .jy {
        display: flex;
        float: none;
        margin-left: 50px;
        width: auto;
        .rate-item {
          padding: 1px 0;
        }
      }
    }

    .scene-bot {
      line-height: 34px;

      span {
        font-size: 12px;
        text-indent: 20px;

        b{
          font-size: 14px;
        }

        &.txt {
          font-size: 12px;
        }
      }

      button {
        flex: 1;
        font-size: 14px;
      }
    }
  }
</style>
