<template>
<div class="radios">
  <div class="radio-controls">
    <audio
      ref="audioPlayer"
      preload="none"
      controls
      :src="streamUrl" >
      Ваш браузер не поддерживает возможность воспроизведения аудио. Попробуйте слушать внешним плеером.
      <a :href="m3uUrl">Плейлист для внешнего плеера</a>
    </audio>
    <div>
      <span
        v-bind:class="{ 'track-title-actived': isPlaying }"
        class="track-title" >
        <marquee hspace="15px" direction="rigth" scrollamount="4">
          {{ title }}
          <span v-if="isPlaying">
            {{ formatDuration(duration) }}
          </span>
        </marquee>
      </span>
      <span
        v-bind:class="{ 'button-play-actived': isPlaying }"
        class="button-play button-custom"
        @click="togglePlay" >
        <b-tooltip label="Пауза/Воспроизведение">
          ⏯
        </b-tooltip>
      </span>
      <span class="button-custom">
        <b-tooltip label="Плюс треку">
          <a href="#" @click="upvoteTrack">💜</a>
        </b-tooltip>
      </span>
      <span class="button-custom">
        <b-tooltip label="Минус треку">
          <a href="#" @click="downvoteTrack">❌</a>
        </b-tooltip>
      </span>
      <span class="button-custom">
        <b-tooltip label="Заказать трек">
          <a href="#" @click="goToOrderTrack">📝</a>
        </b-tooltip>
      </span>
    </div>
    <div>
      <input
        class="volume-slider"
        type="range"
        min="0"
        max="100"
        v-model="volume"
        @change="changeVolume" />
    </div>
  </div>
  <br/>
</div>
</template>

<script>
const axios = require('axios');
const config = require('../../config');

import { formatDuration } from '../utils/duration_formatter'

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
      return formatDuration(value)
    },
    estimateTrack: function (track_id, operator) {
      const data = {};

      data['operator'] = operator;

      axios
        .post(`${config.base_url}/metrics/tracks/${track_id}`, data, { 'headers': { 'Content-type': 'application/json' }})
        .then(() => {
          this.$buefy.toast.open('Отправлено!');
        })
        .catch ((error) => {
          this.$buefy.toast.open(`Ошибка: ${error}`);
        })
    },
    updateMetadata: function() {
      axios
        .get(`${config.base_url}/metrics/info`)
        .then(({ data }) => {
          this.title    = `${data.artist} - ${data.title}`;
          this.track_id = data.id;
          this.estimate = data.estimate;
          this.duration = data.duration;
        })
        .catch((error) => {
          this.$buefy.toast.open(`Ошибка: ${error}`);
        });
    },
    upvoteTrack: function (event) {
      event.preventDefault();
      this.estimateTrack(self.track_id, 'plus');
    },
    downvoteTrack: function (event) {
      event.preventDefault();
      this.estimateTrack(self.track_id, 'minus');
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
    setVolume: function (volume) {
      console.log('volume:', volume);
      this.$refs.audioPlayer.volume = volume / 100;
    },
    changeVolume: function () {
      this.setVolume(this.volume);
    }
  },
  mounted: function() {
    this.$nextTick(function () {
      this.setVolume(this.initialVolume);
    });
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
    display: none;
}

.button-play {
    cursor: pointer;
    font-size: 20px;
    padding: 1px;
    border-radius: 15%;
}

.button-play-actived {
    box-shadow: inset 1px 1px 1px 1px grey;
}

.track-title-actived {
    box-shadow: inset 2px 1px 2px 1px #8e8ed2;
}

.volume-slider {
    width: 280px;
    margin-top: 20px;
}

.track-title {
    background-color: #e8ffff;
    font-size: 15px;
    border: 1px solid black;
    padding: 3px;
    border-radius: 5% 5% 10% 5%;
}

.button-custom {
    margin-left: 20px;
    margin-right: 20px;
}
</style>
