<template>
  <div id="app">
    <template v-if="isAdmin !== null">
      <router-view name="header"></router-view>
      <router-view name="navmenu" class="navmenu"></router-view>
      <div class="body">
        <router-view name="main" class="body-main"></router-view>
      </div>
    </template>
    <router-view></router-view>
  </div>
</template>

<script>
  import {mapState} from 'vuex'

  export default {
    computed: mapState(['isAdmin', 'token'])
  }
</script>

<style lang="less">
  @import "./assets/base";
  html, body, #app {
    height: 100%;
    overflow: hidden;
  }

  #app {
    position: relative;
  }

  .navmenu {
    .fl;
    width: 220px;
    height: 100%;

    .el-menu {
      height: 100%;
      overflow: auto;

      .last {
        margin-bottom: 64px;
      }
    }
  }

  .body {
    margin-left: 220px;
    height: 100%;
    overflow: auto;

    .body-main {
      padding: 20px;
      overflow: auto;
      padding-bottom: 84px;
    }
  }

</style>
