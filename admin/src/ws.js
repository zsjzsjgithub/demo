import store from './store'
import {MessageBox} from 'element-ui'

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
    if (this.ws !== null) {
      this.needTip = false
      this.ws.close();
    }
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
      ws.send({action: 'login', type: 0, token: store.state.token.token})
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
        MessageBox.alert('服务器连接失败，请稍后刷新页面再试！', '服务端错误', {
          confirmButtonText: '刷新',
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
      if (res.action === 'topdata') {
        // 顶部统计
        delete res.action
        store.commit('saveTopData', res)
      }
    }
  },
  onError() {
    console.error('WS Error')
  }
}

export default ws