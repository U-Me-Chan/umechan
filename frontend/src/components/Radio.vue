<template>
<div class="radios">
  <div class="radio-controls">
    <audio ref="audioPlayer" preload="none" controls :src="streamUrl">
      Ваш браузер не поддерживает возможность воспроизведения аудио. Попробуйте слушать внешним плеером.
      <a :href="m3uUrl">Плейлист для внешнего плеера</a>
    </audio>
    <div>
      <span v-bind:class="{ 'button-play-actived': isPlaying }" class="button-play" @click="togglePlay()">⏯</span>
      <span v-bind:class="{ 'track-title-actived': isPlaying }" class="track-title">{{title}}</span>
    </div>
    <span><input class="volume-slider" type="range" min="0" max="100" v-model="volume" @change="setVolume"></span>
  </div>
    <div class="radio-meta">
      <span>🎧: {{listeners}}</span>
      <span>💜: coming soon</span>
      <span>📋: coming soon</span>
      <span>📝: <a href="#" @click="goToThread()">Обсудить</a></span>
  </div>
  <br/>
</div>
</template>

<script>
const axios = require('axios');
const config = require('../../config');

export default {
  name: 'Radio',
  data: function () {
    return {
      title: 'Unknown',
      listeners: 0,
      streamUrl: config.icecast_url + '/stream',
      m3uUrl: config.icecast_url + '/stream.m3u',
      isPlaying: false,
      metadataInterval: null,
      volume: 20
    }
  },
  methods: {
    updateMetadata: function() {
      var self = this;

      axios.get(config.icecast_url + '/status-json.xsl')
        .then((response) => {
          if (Array.isArray(response.data.icestats.source)) {
            if (typeof response.data.icestats.source[1].stream_start !== 'undefined') {
              self.title = 'Прямая трансляция';
              self.listeners = response.data.icestats.source[1].listeners;
            } else {
              self.title = response.data.icestats.source[0].title;
              self.listeners = response.data.icestats.source[0].listeners;
            }
        } else {
            self.title = response.data.icestats.source.title;
            self.listeners = response.data.icestats.source.listeners;
          }
        })
        .catch(() => {
        })
    },
    goToThread: function () {
      event.preventDefault();
      this.$router.push('/thread/34298')
    },
    togglePlay: function () {
      if (this.isPlaying) {
        this.$refs.audioPlayer.pause();
        this.isPlaying = false;

        return;
      }

      this.$refs.audioPlayer.play();
      this.isPlaying = true;

      return;
    },
    setVolume: function () {
      console.log(this.volume, this.volume /100);
      this.$refs.audioPlayer.volume = this.volume / 100;
    }
  },
  created: function () {
    this.metadataInterval = setInterval(() => this.updateMetadata(), 5000);
  },
}
</script>

<style>
h1 {
    text-align: center;
}

audio {
    border: 5px solid grey;
    border-radius: 10% 30% 10% 40%;
    display: none;
}

.radio-meta {
    display: flex;
    flex-direction: column;
    font-size: 12px;
}

.button-play {
    cursor: pointer;
    font-size: 20px;
    padding: 10px;
    border-radius: 15%;
}

.button-play-actived {
    box-shadow: inset 1px 1px 1px 1px grey;
}

.track-title-actived {
    box-shadow: inset 1px 1px 1px 1px #8e8ed2;
}

.volume-slider {
    width: 280px;
    margin-top: 20px;
}

.track-title {
    background-color: #e8ffff;
    padding-top: 15px;
    padding-bottom: 15px;
    padding-left: 10px;
    padding-right: 10px;
    font-size: 15px;
    margin-left: 10px;
    border: 1px solid black;
    border-radius: 5% 5% 10% 5%;
}
</style>
