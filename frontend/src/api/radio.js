import axios from 'axios'

const config = '../../config'


export const putTrackToQueue = (track_id) => {
  return axios.put(config.base_url + '/radio/queue', { track_id: track_id });
}

export const getQueue = () => {
  return axios.get(config.base_url + '/radio/queue');
}

export const getTrackList = (offset, limit, artist_substr, title_substr) => {
  return axios.get(config.base_url + '/radio/tracks', { params: {
    offset: offset,
    limit: limit,
    artist_substr: artist_substr,
    title_substr: title_substr
  }});
}
