<template>
<div class="radios">
  <div class="radio-meta">
    <span>🎵: {{title}}</span>
    <span>🎧: {{listeners}}</span>
    <span>💜: coming soon</span>
    <span>📋: coming soon</span>
    <span>📝: <a href="#" @click="goToThread">Обсудить</a></span>
  </div>
  <br/>
  <audio preload="none" controls :src="stream_url">
    Ваш браузер не поддерживает возможность воспроизведения аудио. Попробуйте слушать внешним плеером.
    <a :href="m3u_list_url">Плейлист для внешнего плеера</a>
  </audio>
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
    },
    goToThread: function () {
      event.preventDefault();
      this.$router.push('/thread/34298')
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
