import Vue from 'vue'
import store from './store'
import Router from 'vue-router'

Vue.use(Router)
const router = new Router({
  'mode': 'history',
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('./views/Login')
    },
    {
      path: '/',
      components: {
        header: () => import('./views/layout/Header'),
        navmenu: () => import('./views/layout/NavMenu'),
        main: () => import('./views/layout/Main')
      },
      children: [
        {
          path: '',
          name: 'home',
          component: () => import('./views/Home')
        },
        {
          path: 'agent',
          name: 'agent',
          component: () => import('./views/Agent')
        },
        {
          path: 'order',
          name: 'order',
          component: () => import('./views/Order')
        },
        {
          path: 'account',
          name: 'account',
          component: () => import('./views/Account')
        },
        {
          path: 'finance',
          name: 'finance',
          component: () => import('./views/Finance')
        },
        {
          path: 'message',
          name: 'message',
          component: () => import('./views/Message')
        },
        {
          path: 'statistic',
          name: 'statistic',
          component: () => import('./views/Statistic')
        },
        {
          path: 'setting',
          name: 'setting',
          component: () => import('./views/Setting')
        },
        {
          path: '*',
          name: 'e404',
          // component: () => import('./components/404')
        }
      ]
    }
  ]
})

let block_agents = [
  'agent',
  'finance',
  'message',
  'setting'
]

router.beforeEach(({name}, from, next) => {
  let token = store.state.token

  if (token) {
    // 登录
    if (name === 'login') {
      next({name: 'home'})
    } else {
      // 代理拦截
      if (!store.state.isAdmin && block_agents.includes(name)) {
        next({name: 'home'})
      } else {
        next()
      }
    }
  } else {
    // 未登录
    if (name === 'login') {
      next()
    } else {
      next({name: 'login'})
    }
  }
})

export default router
