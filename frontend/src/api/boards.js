import axios from 'axios'

const config = require('../../config')

export const getBoardList = () => {
  return axios.get(config.chan_url + '/v2/board');
}

export const getBoardData = (tag,params) => {
  return axios.get(config.chan_url + '/v2/board/' + tag, { params: params});
}
