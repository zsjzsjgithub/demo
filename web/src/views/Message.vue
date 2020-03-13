<template>
  <div class="container">
    <el-tabs type="card" v-model="tabName" @tab-click="gotoRoute">
      <el-tab-pane :label="$t('message.feedback')" name="service"></el-tab-pane>
      <el-tab-pane :label="$t('message.problem')" name="problem"></el-tab-pane>
      <el-tab-pane :label="$t('message.notice')" name="notice"></el-tab-pane>
    </el-tabs>
    <router-view :key="$route.name" v-if="route"></router-view>
  </div>
</template>

<script>
  export default {
    name: 'message',
    data() {
      return {
        tabName: 'service',
        route: true
      }
    },
    methods: {
      gotoRoute() {
        if(this.$route.name === this.tabName) {
          this.route = false
          this.$nextTick(() => {
            this.route = true
          })
        } else {
          this.$router.push({name: this.tabName})
        }
      }
    },
    mounted() {
      switch (this.$route.name) {
        case 'service_detail':
          this.tabName = 'service'
          break;

        case 'problem_detail':
          this.tabName = 'problem'
          break;

        case 'notice_detail':
          this.tabName = 'notice'
          break;

        default:
          this.tabName = this.$route.name
          break;
      }
    }
  }
</script>
