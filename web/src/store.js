import Vue from 'vue'
import Vuex from 'vuex'
import moment from 'moment'

Vue.use(Vuex)

// 计算初始化场次
function getInitScenes() {
  const interval = 6;
  const count = 3;

  const now = moment().startOf('minute')
  let before = now.clone().add({m: 1})
  let time = now.clone().utcOffset('+09:00')
  time.minutes(Math.floor(time.minutes() / interval) * interval)

  let scenes = []
  for (let i = 0; i < count; i++) {
    let sceneTime = time.clone()

    // 判断状态
    let status = 1
    if (sceneTime.isBefore(now) || sceneTime.isSame(now)) {
      status = 3;
    } else if (sceneTime.isBefore(before) || sceneTime.isSame(before)) {
      status = 2;
    }

    scenes.push({
      time: sceneTime.format('YYYY-MM-DD HH:mm:ss'),
      timestamp: Number(sceneTime.format('X')),
      status,
      long: moment(sceneTime - moment()).format('m:ss'),
      price: '',
      type: 0
    })
    time.add({m: interval})
  }

  return scenes
}

export default new Vuex.Store({
  state: {
    token: null,
    lang: '',
    member: null,
    forex: {
      time: '',
      open: '',
      close: '',
      high: '',
      low: ''
    },
    sleep: false,
    scenes: getInitScenes(),
    time: ''
  },
  mutations: {
    saveToken(state, token) {
      state.token = token
    },
    setLang(state, lang) {
      state.lang = lang
    },
    saveMember(state, member) {
      state.member = member
    },
    saveForex(state, forex) {
      state.forex = forex
    },
    saveScenes(state, scenes) {
      state.scenes = scenes
    },
    saveTime(state, time) {
      state.time = time
    },
    setSleep(state, sleep) {
      state.sleep = sleep
    }
  },
  actions: {
    login({commit}, form) {
      return Vue.api.post('/tokens', form).then(data => {
        if (data) {
          localStorage.JWT_FXHOS_TOKEN = JSON.stringify(data)
          commit('saveToken', data)
        }
      })
    },
    register({commit}, form) {
      return Vue.api.post('/tokens/register', form).then(data => {
        if (data) {
          localStorage.JWT_FXHOS_TOKEN = JSON.stringify(data)
          commit('saveToken', data)
        }
      })
    },
    getMemberInfo({commit}) {
      return Vue.api.get('/tokens/info').then(data => {
        if (data) {
          commit('saveMember', data)
        }
      })
    },
    logout({commit}) {
      return Vue.api.delete('/tokens').then(() => {
        commit('saveToken', null)
        localStorage.removeItem('JWT_FXHOS_TOKEN')
      })
    },
    selLang({commit}, lang) {
      localStorage.lang = lang
      commit('setLang', lang)
    }
  },
})
