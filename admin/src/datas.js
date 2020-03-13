import {http} from './axios'

export const agents = () => {
  return http.get('/datas/agents')
}
