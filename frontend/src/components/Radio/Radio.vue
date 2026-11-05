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
    :class="{ 'radio-track-title-active': isPlaying }">
    <marquee hspace="15px" direction="rigth" scrollamount="4">
      {{ title }}
      <span v-if="isPlaying">
        {{ formatDuration(duration) }}
      </span>
    </marquee>
  </div>

  <div class="radio-buttons-wrap">
    <span
      title="Пауза/Воспроизведение"
      class="radio-button-play"
      :class="{ 'radio-button-play-active' : isPlaying }"
      @click="togglePlay">⏯</span>

    <span title="Плюс треку" class="radio-button-upvote" @click="upvoteTrack">💜</span>

    <span title="Минус треку" class="radio-button-downvote" @click="downvoteTrack">❌</span>
  </div>

  <input
    class="radio-volume-slider"
    type="range"
    min="0"
    ref="volumeSlider"
    max="100"
    v-model="volume"
    @change="changeVolume"
  />
</div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import config from '../../../config';
import { formatDuration } from '../../utils/duration_formatter';

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
    const metadataInterval = ref(null);
    const volume = ref(props.initialVolume);
    const track_id = ref(0);
    const estimate = ref(0);
    const duration = ref(1);

    const estimateTrack = (trackId, operator) => {
      const data = {};
      data['operator'] = operator;

      axios.post(`${config.base_url}/metrics/tracks/${trackId}`, data, { 'headers': { 'Content-type': 'application/json'}}).then(() => {
        showToast('Отправлено!');
      }).catch((error) => {
        showToast(`Ошибка: ${error}`);
      });
    };

    const updateMetadata = () => {
      axios.get(`${config.base_url}/metrics/info`)
        .then(({ data }) => {
          title.value = `${data.artist} - ${data.title}`;
          track_id.value = data.id;
          estimate.value = data.estimate;
          duration.value = data.duration;
        })
        .catch((error) => {
          showToast(`Ошибка: ${error}`);
        });
    };

    const upvoteTrack = (event) => {
      event.preventDefault();
      if (!isPlaying.value) return;
      estimateTrack(track_id.value, 'plus');
    };

    const downvoteTrack = (event) => {
      event.preventDefault();
      if (!isPlaying.value) return;
      estimateTrack(track_id.value, 'minus');
    };

    const togglePlay = () => {
      const audioEl = document.querySelector('.radio-default-player');
      if (isPlaying.value) {
        if (audioEl) audioEl.pause();
        if (metadataInterval.value) clearInterval(metadataInterval.value);
      }

      if (!isPlaying.value) {
        if (audioEl) audioEl.play();
        updateMetadata();
        metadataInterval.value = setInterval(() => updateMetadata(), 5000);
      }

      isPlaying.value = !isPlaying.value;
    };

    const changeVolume = (value) => {
      const audioEl = document.querySelector('.radio-default-player');
      if (audioEl) {
        audioEl.volume = value / 100;
      }
      volume.value = value;
      saveVolume(value);
    };

    const getVolume = () => {
      const vol = localStorage.getItem('radio-volume');
      return (vol === 'undefined' || vol === null) ? props.initialVolume : vol;
    };

    const saveVolume = (value) => {
      localStorage.setItem('radio-volume', value);
    };

    const init = () => {
      changeVolume(getVolume());
    };

    const showToast = (message) => {
      alert(message);
    };

    onMounted(() => {
      init();
    });

    onBeforeUnmount(() => {
      if (metadataInterval.value) {
        clearInterval(metadataInterval.value);
      }
    });

    return {
      title,
      listeners,
      streamUrl,
      m3uUrl,
      isPlaying,
      volume,
      track_id,
      estimate,
      duration,
      formatDuration: (value) => formatDuration(value),
      estimateTrack,
      updateMetadata,
      upvoteTrack,
      downvoteTrack,
      togglePlay,
      changeVolume
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

.radio-default-player {
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

.radio-track-title-active {
    box-shadow: inset 2px 1px 2px 1px var(--color-radio-shadow);
}

.radio-buttons-wrap,
.radio-track-title {
    display: flex;
    justify-content: space-around;
    align-items: center;
}

.radio-button-play,
.radio-button-upvote,
.radio-button-downvote {
    cursor: pointer;
    font-size: 20px;
    padding: 1px;
    border-radius: 5px;
    user-select: none;
}

.radio-button-play-active {
    box-shadow: inset 1px 1px 1px 1px grey;
}
</style>
