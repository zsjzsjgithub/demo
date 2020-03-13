<template>
  <div class="msg-content">
    <h1>{{info.title}}</h1>
    <p class="info">{{info.author ? info.author.nickname : ''}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{info.created_at}}</p>
    <div class="content-main">
      <div class="content">{{info.content}}</div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'message-detail',
    data() {
      return {
        info: {}
      }
    },
    methods: {
      getData() {
        this.$api.get(`/messages/${this.$route.params.id}`).then(data => {
          if (data) {
            this.info = data
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
    }
  }
</style>
