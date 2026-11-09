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
import { ref, onMounted } from 'vue';

import axios from 'axios';
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
  setup(props) {
    const title = ref('Включите воспроизведение для обновления информации');
    const listeners = ref(0);
    const streamUrl = ref(`${config.icecast_url}/stream?bypass_cache_hack=${Date.now()}`);
    const m3uUrl = ref(`${config.icecast_url}/stream.m3u`);
    const isPlaying = ref(false);
    const volume = ref(props.initialVolume);
    const audioPlayer = ref(null);
    const volumeSlider = ref(null);
    const metadataInterval = ref(null);

    const track_id = ref(0);
    const estimate = ref(0);
    const duration = ref(1);

    const formatDurationFn = function (value) {
      return formatDuration(value);
    };

    const estimateTrack = function (trackId, operator) {
      const data = {};
      data['operator'] = operator;

      axios.post(`${config.base_url}/metrics/tracks/${trackId}`, data, { 'headers': { 'Content-type': 'application/json'}}).then(() => {
        showToast({ message: 'Отправлено!', type: 'is-success' });
      }).catch((error) => {
        showToast({ message: `Ошибка: ${error}`, type: 'is-danger' });
      });
    };

    const updateMetadata = function() {
      axios.get(`${config.base_url}/metrics/info`)
        .then(({ data }) => {
          title.value    = `${data.artist} - ${data.title}`;
          track_id.value = data.id;
          estimate.value = data.estimate;
          duration.value = data.duration;
        })
        .catch((error) => {
          showToast({ message: `Ошибка: ${error}`, type: 'is-danger' });
        })
    };

    const upvoteTrack = function (event) {
      event.preventDefault();

      if (!isPlaying.value) {
        return;
      }

      estimateTrack(track_id.value, 'plus');
    };

    const downvoteTrack = function (event) {
      event.preventDefault();

      if (!isPlaying.value) {
        return;
      }

      estimateTrack(track_id.value, 'minus');
    };

    const togglePlay = function () {
      if (isPlaying.value) {
        audioPlayer.value.pause();
        clearInterval(metadataInterval.value);
      }

      if (!isPlaying.value) {
        audioPlayer.value.play();
        updateMetadata();
        metadataInterval.value = setInterval(() => updateMetadata(), 5000);
      }

      isPlaying.value = !isPlaying.value;
    };

    const changeVolume = function (value) {
      audioPlayer.value.volume = value / 100;
      volume.value = value;
      saveVolume(value);
    };

    const getVolume = function () {
      const storedVolume = localStorage.getItem('radio-volume');
      return (storedVolume === 'undefined' || storedVolume === null) ? props.initialVolume : storedVolume;
    };

    const saveVolume = function (value) {
      localStorage.setItem('radio-volume', value);
    };

    const init = function () {
      changeVolume(parseFloat(getVolume()));
    };

    onMounted(() => {
      init();
    });

    return {
      title,
      listeners,
      streamUrl,
      m3uUrl,
      isPlaying,
      volume,
      audioPlayer,
      volumeSlider,
      track_id,
      estimate,
      duration,
      formatDuration: formatDurationFn,
      estimateTrack,
      updateMetadata,
      upvoteTrack,
      downvoteTrack,
      togglePlay,
      changeVolume,
      init,
      getVolume,
      saveVolume
    };
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
    background-color: var(--color-radio-title-bg);
    font-size: 15px;
    padding: 5px;
    height: 38px;
    border: 1px solid var(--color-radio-title-border);
    border-radius: 5px;
}

.radio-track-title marquee {
    margin-left: 0;
    margin-right: 0;
}

.radio-track-tittle_active {
    box-shadow: inset 2px 1px 2px 1px var(--color-radio-shadow);
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
