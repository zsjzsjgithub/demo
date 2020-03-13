import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import lib from './lib'
import axios from './axios'
import i18n from './i18n'
import 'element-ui/lib/theme-chalk/index.css';
import ElementUI from 'element-ui'
import moment from 'moment'

Vue.use(ElementUI)
Vue.use(lib)
Vue.use(axios)

Vue.config.productionTip = false

moment.defaultFormat = 'YYYY-MM-DD HH:mm:ss'
window.TZ='+09:00'
window.moment = moment

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
