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
  </div>
</div>
</template>

<script>
const config = require('../../config');
const axios  = require('axios');

export default {
  name: 'FileViewer',
  data() {
    return {
      currentIndex: 0,
      isLoading: false,
      progress: 0,
      timer: null,
      videoTimer: null,
      files: [],
      currentVideoVolume: 0.2
    }
  },
  computed: {
    currentFile() {
      return this.files[this.currentIndex] || null;
    },
    isEmptyFilesList() {
      return this.files.length === 0;
    }
  },
  created: function () {
    axios.get(config.chan_url + '/v2/post/' + this.$route.params.id + '/files').then((response) => {
      this.files = response.data.payload.files
    }).catch((error) => {
      console.error(error);
    });
  },
  mounted: function () {
    if (this.files.length > 0) {
      this.startTimer();
      this.restoreVolume();
    }
  },
  beforeDestroy() {
    this.clearTimers();
  },
  watch: {
    files: {
      handler() {
        this.currentIndex = 0;
        this.clearTimers();
        if (this.files.length > 0) {
          this.startTimer();
          this.restoreVolume();
        }
      },
      deep: true
    },
    currentIndex: {
      handler() {
        this.$nextTick(() => {
          this.restoreVolume();
        });
      }
    }
  },
  methods: {
    startTimer() {
      this.clearTimers();

      if (this.currentFile && this.currentFile.type === 'image') {
        this.progress = 0;
        this.timer = setInterval(() => {
          this.progress += 1;
          if (this.progress >= 100) {
            this.nextFile();
          }
        }, 100);
      }
    },
    clearTimers() {
      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
      }
      if (this.videoTimer) {
        clearInterval(this.videoTimer);
        this.videoTimer = null;
      }
    },
    onImageLoad() {
      this.isLoading = false;
      this.clearTimers();
      this.startTimer();
    },
    onVideoEnd() {
      this.nextFile();
    },
    onFileError() {
      console.error('Ошибка загрузки файла:', this.currentFile?.link);
      this.clearTimers();
      this.nextFile();
    },
    prevFile() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
        this.clearTimers();
        this.startTimer();
      }
    },
    nextFile() {
      if (this.currentIndex < this.files.length - 1) {
        this.currentIndex++;
        this.clearTimers();
        this.startTimer();
      }
    },
    onEndList() {
      this.currentIndex = this.files.length - 1;
    },
    onStartList() {
      this.currentIndex = 0;
    },
    onNavigateToNumber(number) {
      console.debug(this.currentIndex, number);
    },
    shuffleFilesList() {
      this.files.sort(() => Math.random() - 0.5);
    },
    onVolumeChange() {
      if (this.$refs.videoPlayer && this.currentFile?.type === 'video') {
        const volume = this.$refs.videoPlayer.volume;
        const key = `video_volume`;
        localStorage.setItem(key, volume.toString());
        this.currentVideoVolume = volume;
      }
    },
    restoreVolume() {
      if (this.currentFile && this.currentFile.type === 'video' && this.$refs.videoPlayer) {
        const key = `video_volume`;
        const savedVolume = localStorage.getItem(key);

        if (savedVolume !== null) {
          const volume = parseFloat(savedVolume);
          if (volume >= 0 && volume <= 1) {
            this.$refs.videoPlayer.volume = volume;
            this.currentVideoVolume = volume;
          }
        } else {
          this.$refs.videoPlayer.volume = 0.2;
          this.currentVideoVolume = 0.2;
        }
      }
    }
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
    background: rgba(0,0,0,0.7);
    color: white;
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
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.nav-btn:hover:not(:disabled) {
    background: #0056b3;
}

.nav-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.file-info {
    font-size: 16px;
    font-weight: bold;
    min-width: 80px;
    text-align: center;
}

.progress-bar {
    width: 100%;
    height: 4px;
    background: #e0e0e0;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-fill {
    height: 100%;
    background: #007bff;
    transition: width 0.1s linear;
}
</style>
