<template>
<div class="radios">
  <div class="radio-meta">
    <span>🎵: {{title}}</span>
    <span>🎧: {{listeners}}</span>
    <span>💜: coming soon</span>
    <span>📋: coming soon</span>
    <span>📝: <a href="https://scheoble.xyz/thread/34298">Обсудить</a></span>
  </div>
  <br/>
  <audio preload="none" controls :src="stream_url"></audio>
</div>
</template>

<script>
const axios = require('axios');
const config = require('../../config');

export default {
  name: 'Radio',
  data: function () {
    return {
      title: 'Unknown artist - Unknown Track',
      listeners: 0,
      stream_url: config.icecast_url + '/stream',
      m3u_list_url: config.icecast_url + '/stream.m3u'
    }
  },
  methods: {
    updateMetadata: function() {
      var self = this;

      axios.get(config.icecast_url + '/status-json.xsl')
        .then((response) => {
          self.title = response.data.icestats.source.title;
          self.listeners = response.data.icestats.source.listeners;
        })
        .catch(() => {
          self.$buefy.toast.open('Произошла ошибка при запросе данных');
        })
    }
  },
  created: function () {
    this.updateMetadata();
    setInterval(() => this.updateMetadata(), 5000);
  }
}
</script>

<style>
h1 {
    text-align: center;
}

audio {
    border: 5px solid grey;
    border-radius: 10% 30% 10% 40%;
}

.radio-meta {
    display: flex;
    flex-direction: column;
    font-size: 12px;
}
</style>
