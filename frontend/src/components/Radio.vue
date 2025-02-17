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
    <span><a href="#" @click="estimateTrack(track_id, 'plus')">💜: Нраица</a></span>
    <span><a href="#" @click="estimateTrack(track_id, 'minus')">❌: Гавно</a></span>
    <span><a href="#" @click="goToThread()"> 📝: Обсудить</a></span>
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
      volume: 20,
      track_id: 0,
      estimate: 0,
      duration: 1
    }
  },
  methods: {
    estimateTrack: function (track_id, operator) {
      if (this.isPlaying == false) {
        this.$buefy.toast.open('Нельзя оценивать треки, если не слушаешь радио!');

        return;
      }

      var self = this;

      var data = {};
      data['operator'] = operator;

      axios.post(config.base_url + '/metrics/tracks/' + track_id, data, { 'headers': { 'Content-type': 'application/json' }}).then(() => {
        self.$buefy.toast.open('Отправлено!');
      }).catch ((error) => {
        self.$buefy.toast.open(`Ошибка: ${error}`);
      })
    },
    updateMetadata: function() {
      var self = this;

      axios.get(config.base_url + '/metrics/info')
        .then((response) => {
          self.title = response.data.artist + ' - ' + response.data.title;
          self.track_id = response.data.id;
          self.estimate = response.data.estimate;
          self.duration = response.data.duration;
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
    this.updateMetadata();
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
    padding-block: 15px;
    padding-inline: 10px;
    font-size: 15px;
    margin-left: 10px;
    border: 1px solid black;
    border-radius: 5% 5% 10% 5%;
}
</style>
