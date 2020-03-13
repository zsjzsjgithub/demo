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
        main: () => import('./views/layout/Main'),
      },
      children: [
        {
          path: '',
          name: 'home',
          component: () => import('./views/Home')
        },
        {
          path: 'rate',
          name: 'rate',
          component: () => import('./views/Rate')
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
          path: 'message',
          component: () => import('./views/Message'),
          children: [
            {
              path: '',
              name: 'service',
              meta: {
                type: 1
              },
              component: () => import('./views/Service')
            },
            {
              path: 'service/:id',
              name: 'service_detail',
              component: () => import('./views/ServiceDetail')
            },
            {
              path: 'problem',
              name: 'problem',
              meta: {
                type: 2
              },
              component: () => import('./views/MessageList')
            },
            {
              path: 'problem/:id',
              name: 'problem_detail',
              component: () => import('./views/MessageDetail')
            },
            {
              path: 'notice',
              name: 'notice',
              meta: {
                type: 3
              },
              component: () => import('./views/MessageList')
            },
            {
              path: 'notice/:id',
              name: 'notice_detail',
              component: () => import('./views/MessageDetail')
            }
          ]
        },
        {
          path: 'chat',
          name: 'chat',
          component: () => import('./views/Chat')
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

router.beforeEach(({name}, from, next) => {
  let token = store.state.token

  if (token) {
    // 登录
    if (name === 'login') {
      next({name: 'home'})
    } else {
      next()
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
