import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    token: null,
    lang: '',
    member: null,
    isAdmin: null,
    // 首页顶部数据
    topData: {
      deposit: {
        pending: 0,
        waitting: 0,
        completed: 0
      },
      withdrawal: {
        pending: 0,
        waitting: 0,
        completed: 0
      },
      question: 0,
      member: {
        new: 0,
        online: 0
      }
    },
    // 用户首页顶部款项数字点击
    finance: {
      type: '',
      status: '',
      date: []
    },
    // 用户首页顶部提问数字点击
    question: false,
    // 用户首页顶部会员数字点击
    mem: {
      online: false,
      date: []
    }
  },
  mutations: {
    saveToken(state, token) {
      if (token) {
        state.isAdmin = token.user_type === 1
      }
      state.token = token
    },
    setLang(state, lang) {
      state.lang = lang
    },
    saveMember(state, member) {
      state.member = member
    },
    saveTopData(state, data) {
      state.topData = data
    },
    setFinance(state, data) {
      state.finance = data
    },
    setQuestion(state, data) {
      state.question = data
    },
    setMem(state, data) {
      state.mem = data
    }
  },
  actions: {
    login({commit}, form) {
      return Vue.api.post('/tokens', form).then(data => {
        if (data) {
          localStorage.JWT_FXHOS_ADMIN_TOKEN = JSON.stringify(data)
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
        localStorage.removeItem('JWT_FXHOS_ADMIN_TOKEN')
      })
    },
    selLang({commit}, lang) {
      localStorage.lang = lang
      commit('setLang', lang)
    }
  }
})
