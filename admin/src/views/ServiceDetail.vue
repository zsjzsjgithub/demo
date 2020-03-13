<template>
  <div class="msg-content">
    <h1>{{info.title}}</h1>
    <p class="info">{{info.author ? info.author.nickname : ''}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{info.created_at}}</p>
    <div class="content-main">
      <div class="content">{{info.content}}</div>
      <div class="reply">
        <div class="title">{{$t('replies', [info.replies ? info.replies.length : 0])}}</div>
        <div class="reply-item" v-for="r in info.replies">
          <div class="at">
            <div class="time">{{r.created_at}}</div>
            <b>{{r.author ? r.author.nickname : ''}}</b>
          </div>
          <div class="rcontent">{{r.content}}</div>
        </div>
        <div class="text">
          <div class="input">
            <el-input type="textarea" v-model="content" :autosize="{minRows: 1}" resize="none" :placeholder="$t('reply')"></el-input>
          </div>
          <div class="btn">
            <el-button type="primary" size="small" @click="submit" v-t="'submit'"></el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'service-detail',
    props: {
      id: Number
    },
    data() {
      return {
        info: {},
        content: ''
      }
    },
    methods: {
      getData() {
        this.$api.get(`/messages/${this.id}`).then(data => {
          if (data) {
            this.info = data
          }
        })
      },
      submit() {
        this.$api.post(`/messages/${this.id}/reply`, {content: this.content}).then(data => {
          if (data) {
            this.$emit('close')
          }
        })
      }
    },
    mounted() {
      this.getData()
    }
  }
</script>

<style scoped lang="less">
  @import "../assets/config";

  .msg-content {
    width: 800px;
    margin: 0 auto;

    h1 {
      text-align: center;
      font-size: 24px;
      line-height: 40px;
      margin-top: 20px;
    }

    p.info {
      text-align: right;
      color: #777;
      margin-bottom: 10px;
      font-size: 12px;
      line-height: 18px;
    }

    .content-main {
      border-radius: 4px;
      background: #fafafa;
      padding: 20px;

      .content {
        line-height: 28px;
        white-space: pre-wrap;
        font-size: 14px;
      }

      .reply {
        margin-top: 30px;
        background: #fff;
        border: 1px solid #ebebeb;
        box-shadow: 0 1px 3px rgba(26,26,26,.1);
        border-radius: 4px;

        .title {
          font-weight: bold;
          padding: 10px;
          font-size: 14px;
          border-bottom: 1px solid #ebebeb;
          margin-bottom: 10px;
        }

        .reply-item {
          line-height: 28px;
          padding: 0 26px;
          margin-bottom: 10px;
          color: #606266;

          .at {
            line-height: 22px;
            margin-bottom: 6px;

            b {
              color: #202020;
            }

            .time {
              .fr;

              font-size: 12px;
            }
          }

          .rcontent {
            line-height: 22px;
            white-space: pre-wrap;
            padding-bottom: 10px;
            border-bottom: 1px solid #ebebeb;
          }
        }

        .text {
          position: relative;
          padding: 0 26px;
          padding-bottom: 20px;
          margin-top: 20px;

          .input {
            margin-right: 80px;
          }

          .btn {
            position: absolute;
            bottom: 20px;
            right: 26px;
          }
        }
      }
    }
  }
</style>
