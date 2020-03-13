import api from 'axios'
import store from './store'
import router from './router'
import {Loading, Message} from 'element-ui'
import i18n from './i18n'

api.defaults.baseURL = process.env.VUE_APP_API_HOST

let isRefreshing = false // 是否正在刷新token，标记以避免重复刷新
let requests = [] // 需要刷新token的请求列表
let loading
const noLoading = [ // 不显示loading的请求
  '/tokens',
  '/tokens/info',
  '/trades'
]
api.interceptors.request.use(config => {
  config.headers['Accept-Language'] = store.state.lang
  if (!noLoading.includes(config.url)) {
    loading = Loading.service({
      fullscreen: true,
      lock: true
    })
  }
  let token = store.state.token
  if (token) {
    let now = new Date().getTime() / 1000

    // 判断是否需要刷新token
    if (config.url !== '/tokens' && now >= token.expired_at) {
      // 刷新token，防止多个连接并发
      if (!isRefreshing) {
        isRefreshing = true
        api.request({
          url: '/tokens',
          method: 'put',
          headers: {Authorization: token.type + ' ' + token.token}
        }).then(res => {
          let newToken = null
          if (res) {
            newToken = {
              token: res.token,
              type: res.type,
              expired_at: res.expired_at
            }
            localStorage.JWT_FXHOS_TOKEN = JSON.stringify(newToken)
            store.commit('saveToken', newToken)
          }
          requests.forEach(requestItem => {
            let {resolve, conf} = requestItem
            if (newToken) {
              conf.headers['Authorization'] = newToken.type + ' ' + newToken.token
            }
            resolve(conf)
          })
          requests = []
          isRefreshing = false
        })
      }

      // 收集需要刷新token的请求，待token刷新完毕时resolve触发
      return new Promise(resolve => {
        requests.push({resolve, conf: config})
      })
    }

    // 无需刷新的token直接设置并返回
    config.headers['Authorization'] = token.type + ' ' + token.token
  }
  return config
}, error => {
  loading && loading.close()
  // 对请求错误做些什么
  return Promise.reject(error)
})

api.interceptors.response.use(res => {
  loading && loading.close()
  let {data} = res
  if (data.code !== 200) {
    if (data.code === 401) {
      Message.error('登录超时，请重新登录')
      localStorage.removeItem('JWT_FXHOS_TOKEN')
      global.location.reload()
    } else if (data.code === 404) {
      router.push('/404')
    } else if (data.code === 422) {
      for (const key in data.data) {
        let err = data.data[key]
        if (typeof err === 'string') Message.error(err)
        else Message.error(err[0])
        break
      }
    } else if (data.code === 500 && typeof data.data === 'string') {
      Message.error(data.data)
    } else {
      Message.error(i18n.t('base.error'))
    }
  } else {
    loading && loading.close()
    return data.data || true
  }
})

export default {
  install(Vue) {
    Vue.api = api
    Vue.prototype.$api = api
  }
}
