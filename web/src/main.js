import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import lib from './lib'
import axios from './axios'
import i18n from './i18n'
import './plugins/element.js'

Vue.use(lib)
Vue.use(axios)

Vue.config.productionTip = false

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
