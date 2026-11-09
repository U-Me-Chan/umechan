<template>
<div class="file-viewer">
  <div class="file-container" v-if="currentFile">

    <div class="navigation">
      <button
        @click="onStartList"
        class="nav-btn">start</button>
      <button
        @click="prevFile"
        :disabled="currentIndex <= 0"
        class="nav-btn">←</button>

      <span class="file-info">
        {{currentIndex + 1}} / {{ files.length }}
      </span>

      <button
        @click="nextFile"
        :disabled="currentIndex >= files.length - 1"
        class="nav-btn">→</button>

      <button
        @click="onEndList"
        class="nav-btn">end</button>
    </div>
    <img
      v-if="currentFile.type === 'image'"
      :src="currentFile.link"
      alt="Файл"
      class="file-image"
      @load="onImageLoad"
      @error="onFileError"
      >

    <video
      v-else-if="currentFile.type === 'video'"
      :src="currentFile.link"
      class="file-video"
      controls
      autoplay
      @ended="onVideoEnd"
      @error="onFileError"
      @volumechange="onVolumeChange"
      ref="videoPlayer"
      >
      Ваш браузер не поддерживает видео тег
    </video>

    <div v-if="isLoading && currentFile.type === 'image'" class="loading">
      Загрузка...
    </div>
  </div>

  <div v-if="currentFile && currentFile.type === 'image'" class="progress-bar">
    <div
      class="progress-fill"
      :style="{ width: progress + '%' }"
      ></div>
  </div>

  <div v-if="isEmptyFilesList">
    Пусто!
  </div>

  <div v-if="!isEmptyFilesList">
    <button @click="shuffleFilesList">Shuffle</button>
    <button @click="reverseFileList">Reverse</button>
  </div>
</div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
const config = require('../../config');

export default {
  name: 'FileViewer',
  setup() {
    const route = useRoute();

    const currentIndex = ref(0);
    const isLoading = ref(false);
    const progress = ref(0);
    const files = ref([]);
    const videoPlayer = ref(null);

    const currentVideoVolume = ref(0.2);

    const currentFile = computed(() => files.value[currentIndex.value] || null);
    const isEmptyFilesList = computed(() => files.value.length === 0);

    let timer = null;
    let videoTimer = null;

    const clearTimers = function () {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
      if (videoTimer) {
        clearInterval(videoTimer);
        videoTimer = null;
      }
    };

    const startTimer = function () {
      clearTimers();

      if (currentFile.value && currentFile.value.type === 'image') {
        progress.value = 0;
        timer = setInterval(() => {
          progress.value += 1;
          if (progress.value >= 100) {
            nextFile();
          }
        }, 100);
      }
    };

    const onImageLoad = function () {
      isLoading.value = false;
      clearTimers();
      startTimer();
    };

    const onVideoEnd = function () {
      nextFile();
    };

    const onFileError = function () {
      console.error('Ошибка загрузки файла:', currentFile.value?.link);
      clearTimers();
      nextFile();
    };

    const prevFile = function () {
      if (currentIndex.value > 0) {
        currentIndex.value--;
        clearTimers();
        startTimer();
      }
    };

    const nextFile = function () {
      if (currentIndex.value < files.value.length - 1) {
        currentIndex.value++;
        clearTimers();
        startTimer();
      }
    };

    const onEndList = function () {
      currentIndex.value = files.value.length - 1;
    };

    const onStartList = function () {
      currentIndex.value = 0;
    };

    const shuffleFilesList = function () {
      files.value.sort(() => Math.random() - 0.5);
    };

    const reverseFileList = function () {
      files.value.reverse();
    };

    const onVolumeChange = function () {
      if (videoPlayer.value && currentFile.value?.type === 'video') {
        const volume = videoPlayer.value.volume;
        const key = `video_volume`;
        localStorage.setItem(key, volume.toString());
        currentVideoVolume.value = volume;
      }
    };

    const restoreVolume = function () {
      if (currentFile.value && currentFile.value.type === 'video' && videoPlayer.value) {
        const key = `video_volume`;
        const savedVolume = localStorage.getItem(key);

        if (savedVolume !== null) {
          const volume = parseFloat(savedVolume);
          if (volume >= 0 && volume <= 1) {
            videoPlayer.value.volume = volume;
            currentVideoVolume.value = volume;
          }
        } else {
          videoPlayer.value.volume = 0.2;
          currentVideoVolume.value = 0.2;
        }
      }
    };

    onMounted(() => {
      axios.get(config.chan_url + '/v2/post/' + route.params.id + '/files').then((response) => {
        files.value = response.data.payload.files;
      }).catch((error) => {
        console.error(error);
      });
    });

    onBeforeUnmount(() => {
      clearTimers();
    });

    watch(files, {
      handler() {
        currentIndex.value = 0;
        clearTimers();
        if (files.value.length > 0) {
          startTimer();
          restoreVolume();
        }
      },
      deep: true
    });

    watch(currentIndex, () => {
      nextTick(() => {
        restoreVolume();
      });
    });

    return {
      currentIndex,
      isLoading,
      progress,
      files,
      currentVideoVolume,
      currentFile,
      isEmptyFilesList,
      videoPlayer,
      startTimer,
      clearTimers,
      onImageLoad,
      onVideoEnd,
      onFileError,
      prevFile,
      nextFile,
      onEndList,
      onStartList,
      shuffleFilesList,
      reverseFileList,
      onVolumeChange,
      restoreVolume
    };
  }
}
</script>

<style scoped>
.file-viewer {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    margin: 0 auto;
}

.file-container {
    position: relative;
    width: 100%;
    text-align: center;
    margin-bottom: 20px;
}

.file-image {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
}

.file-video {
    width: 100%;
    max-width: 100%;
    max-height: 80vh;
    height: auto;
}

.loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--color-toggle-form-bg);
    color: var(--color-btn-text);
    padding: 10px 20px;
    border-radius: 5px;
}

.navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.nav-btn {
    padding: 5px 10px;
    background: var(--color-navigation-bg);
    color: var(--color-navigation-text);
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.nav-btn:hover:not(:disabled) {
    background: var(--color-navigation-hover);
}

.nav-btn:disabled {
    background: var(--color-navigation-disabled);
    cursor: not-allowed;
}

.file-info {
    font-size: 16px;
    font-weight: bold;
    min-width: 80px;
    text-align: center;
    color: var(--color-text);
}

.progress-bar {
    width: 100%;
    height: 4px;
    background: var(--color-progress-bg);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-fill {
    height: 100%;
    background: var(--color-progress-fill);
    transition: width 0.1s linear;
}
</style>
