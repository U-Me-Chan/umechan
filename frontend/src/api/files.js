import axios from 'axios'

const config = require('../../config')

export const getFiles = (params) => {
  return axios.get(config.filestore_url + '/files', { params: params });
}

export const deleteFile = (name, params) => {
  return axios.delete(config.filestore_url + '/files/' + name, params);
}

export const uploadFile = (uploadData) => {
  return axios.post(config.filestore_url, uploadData, { 'headers': { 'Content-Type': 'multipart/form-data' }});
}
