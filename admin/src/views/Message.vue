<template>
  <div>
    <el-tabs type="card" v-model="tabName">
      <el-tab-pane :label="$t('service')" name="service">
        <Service v-if="tabName === 'service'"></Service>
      </el-tab-pane>
      <el-tab-pane :label="$t('problem')" name="problem">
        <List v-if="tabName === 'problem'" :type="2"></List>
      </el-tab-pane>
      <el-tab-pane :label="$t('notice')" name="notice">
        <List v-if="tabName === 'notice'" :type="3"></List>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
  import Service from './Service'
  import List from './MessageList'
  import {mapState} from 'vuex'

  export default {
    name: 'message',
    components: {
      Service,
      List
    },
    data() {
      return {
        tabName: 'service'
      }
    },
    computed: mapState(['question']),
    watch: {
      question: {
        handler(v) {
          if (v) {
            this.tabName = 'service'
          }
        },
        immediate: true
      }
    }
  }
</script>
