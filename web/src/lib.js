// 获取token
import store from './store';

// 载入语言
import elCN from 'element-ui/lib/locale/lang/zh-CN'
import elKR from 'element-ui/lib/locale/lang/ko'
import local from 'element-ui/lib/locale'

let elLang = {
  zh_CN: elCN,
  ko_KR: elKR
}
let lang = localStorage.lang
if (lang) {
  local.use(elLang[lang])
  store.commit('setLang', lang)
} else {
  store.dispatch('selLang', process.env.VUE_APP_DEFAULT_LANG)
  local.use(elLang[process.env.VUE_APP_DEFAULT_LANG])
}

// 载入token
let token = localStorage.JWT_FXHOS_TOKEN
if (token) {
  store.commit('saveToken', JSON.parse(token))
}

// 时间格式化
Date.prototype.format = function (fmt) {
  if (!fmt) {
    fmt = 'yyyy-MM-dd hh:mm:ss'
  }
  const o = {
    "M+": this.getMonth() + 1, //月份
    "d+": this.getDate(), //日
    "h+": this.getHours(), //小时
    "m+": this.getMinutes(), //分
    "s+": this.getSeconds(), //秒
    "q+": Math.floor((this.getMonth() + 3) / 3), //季度
    "S": this.getMilliseconds() //毫秒
  };
  if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
  for (let k in o)
    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
  return fmt;
}

export default {
  install(Vue) {
    // 解析后端UTC时间
    Vue.prototype.parseDate = (dateTime, fmt) => {
      if (!dateTime) {
        return ''
      }
      dateTime = dateTime.replace(' ', 'T') + 'Z';
      if (!fmt) {
        fmt = 'yyyy-MM-dd hh:mm:ss'
      }
      return new Date(dateTime).format(fmt)
    }

    Vue.filter('dateFormat', (date, fmt) => {
      data.format(fmt)
    })

    Vue.filter('numFormat', num => {
      if (!num) {
        return '0'
      }
      num = parseFloat(num)
      return (num.toString().indexOf ('.') !== -1) ? num.toLocaleString() : num.toString().replace(/(\d)(?=(?:\d{3})+$)/g, '$1,');
    })

    Vue.filter('dft', (value, f) => {
      if (!value) {
        return f || ''
      }
      return value
    })
  }
}
