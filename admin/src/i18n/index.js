import Vue from 'vue'
import I18n from 'vue-i18n'
import zh_CN from './zh_CN'
import ko_KR from './ko_KR'
import stroe from '../store'

Vue.use(I18n)

export default new I18n({
  locale: stroe.state.lang,
  messages: {
    zh_CN,
    ko_KR
  }
})
