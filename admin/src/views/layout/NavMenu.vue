<template>
  <div>
    <el-menu
        router
        class="el-menu-vertical-demo"
        background-color="#20323E"
        text-color="#fff"
        :default-active="$route.path">
      <el-menu-item index="/">
        <i class="el-icon-star-off"></i>
        <span slot="title" v-t="'menu_member'"></span>
      </el-menu-item>
      <el-menu-item index="/agent" v-if="isAdmin">
        <i class="el-icon-goods"></i>
        <span slot="title" v-t="'menu_agent'"></span>
      </el-menu-item>
      <el-menu-item index="/order">
        <i class="el-icon-time"></i>
        <span slot="title" v-t="'menu_order'"></span>
      </el-menu-item>
      <el-menu-item index="/account">
        <i class="el-icon-tickets"></i>
        <span slot="title" v-t="'menu_account'"></span>
      </el-menu-item>
      <el-menu-item index="/finance" v-if="isAdmin">
        <i class="el-icon-tickets"></i>
        <span slot="title" v-t="'menu_finance'"></span>
      </el-menu-item>
      <el-menu-item index="/message" v-if="isAdmin">
        <i class="el-icon-service"></i>
        <span slot="title" v-t="'menu_message'"></span>
      </el-menu-item>
      <el-menu-item index="/statistic" :class="{last: !isAdmin}">
        <i class="el-icon-sort"></i>
        <span slot="title" v-t="'menu_statistic'"></span>
      </el-menu-item>
      <el-menu-item index="/setting" class="last" v-if="isAdmin">
        <i class="el-icon-setting"></i>
        <span slot="title" v-t="'menu_setting'"></span>
      </el-menu-item>
    </el-menu>
  </div>
</template>

<script>
  import {mapState} from 'vuex'

  export default {
    name: 'nav-menu',
    computed: mapState(['isAdmin'])
  }
</script>
