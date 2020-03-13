import store from './store'
import {MessageBox} from 'element-ui'
import i18n from './i18n'
import router from './router'

const ws = {
  ws: null,
  needTip: false,
  host: process.env.VUE_APP_WS_HOST,
  si: null,
  retry: 0,
  init(r) {
    if (this.ws === null || r) {
      this.needTip = true
      this.ws = new WebSocket(this.host);
      this.ws.onopen = event.onOpen
      this.ws.onclose = event.onClose
      this.ws.onmessage = event.onMessage
      this.ws.onerror = event.onError
    }
  },
  close() {
    this.needTip = false
    this.ws.close();
  },
  send(data) {
    if (typeof data === 'object') {
      data = JSON.stringify(data)
    }
    this.ws.send(data);
  }
}

const event = {
  onOpen() {
    if (store.state.token) {
      ws.send({action: 'login', type: 1, token: store.state.token.token})
    }
    if (ws.retry > 0) {
      ws.retry = 0
      console.info('%c重连成功', 'color: green')
    }
    // 发送心跳，保持连接
    ws.si = setInterval(() => {
      ws.send({heartbeat: true})
    }, 50000)
  },
  onClose() {
    if (ws.needTip === true) {
      if (ws.retry >= 10) {
        MessageBox.alert(i18n.t('ws.server_errmsg'), i18n.t('ws.server_error'), {
          confirmButtonText: i18n.t('base.reload'),
          type: 'error',
          showClose: false,
          closeOnHashChange: false,
          callback: () => {
            global.location.reload()
          }
        })
      } else {
        console.log(`3秒后进行第${++ws.retry}次重连`)
        setTimeout(() => {
          ws.init(true)
        }, 3000)
      }
    } else {
      ws.ws = null
    }

    clearInterval(ws.si)
  },
  onMessage({data}) {
    let res = JSON.parse(data);

    if (res.hasOwnProperty('action')) {
      // 汇率数据
      if (res.action === 'forexdata') {
        if (!res.forex || !res.forex.time) {
          store.commit('setSleep', true)
        } else {
          store.commit('saveForex', res.forex)
          store.commit('saveScenes', res.scenes)
          store.commit('saveTime', res.now)
          store.commit('setSleep', false)
        }
      }

      // 新消息提醒
      if (res.action === 'newchat') {
        MessageBox.alert(i18n.t('chat.content'), i18n.t('chat.title'), {
          type: 'warning',
          confirmButtonText: i18n.t('chat.btn'),
          showClose: false
        }).then(a => {
          if (a === 'confirm') {
            router.push(`/chat?t=${new Date().getTime()}`)
          }
        })
      }
    }
  },
  onError() {
    console.error('WS Error')
  }
}

export default ws