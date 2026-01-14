import axios from 'axios';

const config = require('../../config')

export const getThreadData = (id) => {
  return axios.get(`${config.chan_url}/v2/post/${id}/?no_board_list=true`);
}

export const createReply = (outputData, thread_id) => {
  return axios.put(`${config.chan_url}/v2/post/${thread_id}`, outputData);
}

export const createThread = (outputData) => {
  return axios.post(`${config.chan_url}/v2/post`, outputData);
}

export const deletePost = (id, password) => {
  return axios.delete(`${config.chan_url}/v2/post/${id}`, { params: { password: password}});
}

export const erasePost = (id, reason, admin_key) => {
  return axios.post(`${config.chan_url}/_/v2/post/${id}`, { reason: reason }, {
    headers: {
      'Key': admin_key,
      'Content-type': 'application/json;charset=utf-8',
      'Accept': 'application/json;charset=utf-8'
    }
  });
}
