<template>
  <div class="rate-item" :class="[{selItem: value.number > 0}, `t-${type}`]">
    <div class="price">{{value.price|numFormat}}</div>
    <div class="btn" :class="{read: readonly}">
      <button @click="sub" :class="{dis: value.number <= 0 || disabled}" :disabled="readonly || value.number <= 0 || disabled">-</button>
      <input type="text" :value="value.number" placeholder="0" readonly>
      <button @click="add" :class="{dis: isMax || disabled}" :disabled="readonly || isMax || disabled">+</button>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'rate-item',
    props: {
      value: {
        type: Object,
        default: {
          price: 0,
          number: 0
        }
      },
      type: Number,
      readonly: {
        type: Boolean,
        default: false
      },
      disabled: {
        type: Boolean,
        default: false
      },
      isMax: {
        type: Boolean,
        default: false
      }
    },
    methods: {
      add() {
        this.$emit('input', {
          price: this.value.price,
          number: this.value.number + 1
        })
      },
      sub() {
        this.$emit('input', {
          price: this.value.price,
          number: Math.max(this.value.number - 1, 0)
        })
      }
    }
  }
</script>

<style scoped lang="less">
  @import "../assets/config";

  .rate-item {
    display: block;
  }
  .price {
    .tc;
    display: block;
    color: #666;
    font-size: 16px;
    line-height: 28px;
  }

  .btn {
    background: #eee;
    border-radius: 2px;
    color: #4a4a4a;
    height: 24px;
    width: 74px;
    margin: 0 auto;
    overflow: hidden;

    button, input {
      .fl;
      .tc;
      outline: none;
    }

    button {
      background: none;
      border: none;
      color: #4a4a4a;
      width: 24px;
      padding: 0;
      height: 24px;
      line-height: 24px;
      cursor: pointer;
      &:hover {
        background: #ccc;
      }

      &.dis {
        color: #ccc;
        background: none;
        cursor: not-allowed;
      }
    }

    input {
      background: #fff;
      border: none;
      width: 26px;
      padding: 0;
      height: 22px;
      line-height: 22px;
      margin-top: 1px;
      color: #999;
    }

    &.read {
      button {
        color: #eee;
        background: none;
        cursor: auto;

        &:hover {
          background: none;
        }
      }

      input {
        background: #eee;
      }
    }
  }

  .selItem {
    &.t-1 {
      .price {
        color: @color_buy;
      }

      .btn input {
        color: @color_buy;
        font-weight: bold;
      }
    }

    &.t-2 {
      .price {
        color: @color_sell;
      }

      .btn input {
        color: @color_sell;
        font-weight: bold;
      }
    }

    &.t-3 {
      .price {
        color: @color_cancel;
      }

      .btn input {
        color: @color_cancel;
        font-weight: bold;
      }
    }
  }


  @media screen and (max-width: 1200px) {
    .price {
      font-size: 12px;
      line-height: 14px;
    }

    .btn {
      height: 20px;
      width: 58px;
      margin: 0 2px;

      button {
        width: 20px;
        height: 20px;
        line-height: 20px;
      }

      input {
        width: 18px;
        height: 18px;
        line-height: 18px;
        margin-top: 1px;
      }
    }
  }
</style>
