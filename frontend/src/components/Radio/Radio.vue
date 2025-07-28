<template>
<div class="radio-wrap">
  <audio
    class="radio-default-player"
    ref="audioPlayer"
    preload="none"
    :src="streamUrl">
    Ваш браузер не поддерживает возможность воспроизведения аудио. Попробуйте слушать внешним плеером.
    <a :href="m3uUrl">Плейлист для внешнего плеера</a>
  </audio>

  <div
    class="radio-track-title"
    v-bind:class="{ 'radio-track-tittle-active': isPlaying }">
    <marquee hspace="15px" direction="rigth" scrollamount="4">
      {{ title }}
      <span v-if="isPlaying">
        {{ formatDuration(duration) }}
      </span>
    </marquee>
  </div>

  <div class="radio-buttons-wrap">
    <b-tooltip
      label="Пауза/Воспроизведение"
      class="radio-button-play"
      :class="{ 'radio-button-play-active' : isPlaying }">
      <a href="#" @click="togglePlay">⏯</a>
    </b-tooltip>

    <b-tooltip label="Плюс треку">
      <a href="#" @click="upvoteTrack">💜</a>
    </b-tooltip>

    <b-tooltip label="Минус треку">
      <a href="#" @click="downvoteTrack">❌</a>
    </b-tooltip>

    <b-tooltip label="Заказать трек">
      <a href="#" @click="goToOrderTrack">📝</a>
    </b-tooltip>
  </div>

  <input
    class="radio-volume-slider"
    type="range"
    min="0"
    ref="volumeSlider"
    max="100"
    v-model="volume"
    @change="changeVolume(volume)"/>
</div>
</template>

<script>
const axios = require('axios');
const config = require('../../../config');
import { formatDuration } from '../../utils/duration_formatter'

export default {
  name: 'Radio',
  props: {
    initialVolume: {
      type: String,
      default: '20'
    }
  },
  data: function () {
    return {
      title: 'Включите воспроизведение для обновление информации',
      listeners: 0,
      streamUrl: `${config.icecast_url}/stream`,
      m3uUrl: `${config.icecast_url}/stream.m3u`,
      isPlaying: false,
      metadataInterval: null,
      volume: this.initialVolume,
      track_id: 0,
      estimate: 0,
      duration: 1
    }
  },
  methods: {
    formatDuration: function (value) {
      return formatDuration(value);
    },
    estimateTrack: function (track_id, operator) {
      const data = {};
      data['operator'] = operator;

      axios.post(`${config.base_url}/metrics/tracks/${track_id}`, data, { 'headers': { 'Content-type': 'application/json'}}).then(() => {
        this.$buefy.toast.open('Отправлено!');
      }).catch((error) => {
        this.$buefy.toast.open(`Ошибка: ${error}`);
      });
    },
    updateMetadata: function() {
      axios.get(`${config.base_url}/metrics/info`)
        .then(({ data }) => {
          this.title    = `${data.artist} - ${data.title}`;
          this.track_id = data.id;
          this.estimate = data.estimate;
          this.duration = data.duration;
        })
        .catch((error) => {
          this.$buefy.toast.open(`Ошибка: ${error}`);
        })
    },
    upvoteTrack: function (event) {
      event.preventDefault();
      this.estimateTrack(this.track_id, 'plus');
    },
    downvoteTrack: function (event) {
      event.preventDefault();
      this.estimateTrack(this.track_id, 'minus');
    },
    goToOrderTrack: function (event) {
      event.preventDefault();
      this.$router.push('/tracks');
    },
    togglePlay: function () {
      if (this.isPlaying) {
        this.$refs.audioPlayer.pause();
        clearInterval(this.metadataInterval);
      }

      if (!this.isPlaying) {
        this.$refs.audioPlayer.play();
        this.updateMetadata();
        this.metadataInterval = setInterval(() => this.updateMetadata(), 5000);
      }

      this.isPlaying = !this.isPlaying;
    },
    changeVolume: function (value) {
      this.$refs.audioPlayer.volume = value / 100;
      this.volume = value;
      this.saveVolume(value);
    },
    init: function () {
      this.changeVolume(this.getVolume());
    },
    getVolume: function () {
      const volume = localStorage.getItem('radio-volume');

      return (volume === 'undefined' || volume === null) ? this.initialVolume : volume;
    },
    saveVolume: function (value) {
      localStorage.setItem('radio-volume', value);
    }
  },
  mounted: function () {
    this.init();
  }
}
</script>

<style scoped>

.radio-wrap {
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 10px;
}

radio-default-player {
    display: none;
}

.radio-volume-slider {
    width: 100%;
}

.radio-track-title {
    background-color: #e8ffff;
    font-size: 15px;
    padding: 5px;
    height: 38px;
    border: 1px solid black;
    border-radius: 5px;
}

.radio-track-title marquee {
    margin-left: 0;
    margin-right: 0;
}

.radio-track-title_active {
    box-shadow: inset 2px 1px 2px 1px #8e8ed2;
}

.radio-buttons-wrap,
.radio-track-title {
    display: flex;
    justify-content: space-around;
    align-items: center;
}

radio-button-play {
    cursor: pointer;
    font-size: 20px;
    padding: 1px;
    border-radius: 5px;
}
.radio-button-play-active {
    box-shadow: inset 1px 1px 1px 1px grey;
}
</style>
