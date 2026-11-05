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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import config from '../../config';

export default {
  name: 'FileViewer',
  setup() {
    const route = useRoute();
    const currentIndex = ref(0);
    const isLoading = ref(false);
    const progress = ref(0);
    const timer = ref(null);
    const videoTimer = ref(null);
    const files = ref([]);
    const currentVideoVolume = ref(0.2);
    const videoPlayer = ref(null);

    const currentFile = computed(() => {
      return files.value[currentIndex.value] || null;
    });

    const isEmptyFilesList = computed(() => {
      return files.value.length === 0;
    });

    const startTimer = () => {
      clearTimers();

      if (currentFile.value && currentFile.value.type === 'image') {
        progress.value = 0;
        timer.value = setInterval(() => {
          progress.value += 1;
          if (progress.value >= 100) {
            nextFile();
          }
        }, 100);
      }
    };

    const clearTimers = () => {
      if (timer.value) {
        clearInterval(timer.value);
        timer.value = null;
      }
      if (videoTimer.value) {
        clearInterval(videoTimer.value);
        videoTimer.value = null;
      }
    };

    const onImageLoad = () => {
      isLoading.value = false;
      clearTimers();
      startTimer();
    };

    const onVideoEnd = () => {
      nextFile();
    };

    const onFileError = () => {
      console.error('Ошибка загрузки файла:', currentFile.value?.link);
      clearTimers();
      nextFile();
    };

    const prevFile = () => {
      if (currentIndex.value > 0) {
        currentIndex.value--;
        clearTimers();
        startTimer();
      }
    };

    const nextFile = () => {
      if (currentIndex.value < files.value.length - 1) {
        currentIndex.value++;
        clearTimers();
        startTimer();
      }
    };

    const onEndList = () => {
      currentIndex.value = files.value.length - 1;
    };

    const onStartList = () => {
      currentIndex.value = 0;
    };

    const shuffleFilesList = () => {
      files.value.sort(() => Math.random() - 0.5);
    };

    const reverseFileList = () => {
      files.value.reverse();
    };

    const onVolumeChange = () => {
      const videoEl = document.querySelector('video');
      if (videoEl && currentFile.value?.type === 'video') {
        const vol = videoEl.volume;
        const key = 'video_volume';
        localStorage.setItem(key, vol.toString());
        currentVideoVolume.value = vol;
      }
    };

    const restoreVolume = () => {
      const videoEl = document.querySelector('video');
      if (currentFile.value && currentFile.value.type === 'video' && videoEl) {
        const key = 'video_volume';
        const savedVolume = localStorage.getItem(key);

        if (savedVolume !== null) {
          const vol = parseFloat(savedVolume);
          if (vol >= 0 && vol <= 1) {
            videoEl.volume = vol;
            currentVideoVolume.value = vol;
          }
        } else {
          videoEl.volume = 0.2;
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

      if (files.value.length > 0) {
        startTimer();
        restoreVolume();
      }
    });

    onBeforeUnmount(() => {
      clearTimers();
    });

    return {
      currentIndex,
      isLoading,
      progress,
      files,
      currentFile,
      isEmptyFilesList,
      videoPlayer,
      onImageLoad,
      onVideoEnd,
      onFileError,
      prevFile,
      nextFile,
      onEndList,
      onStartList,
      shuffleFilesList,
      reverseFileList,
      onVolumeChange
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
