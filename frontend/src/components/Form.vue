<template>
<div class="box" @paste="handlePasteEvent">
  <div class="form__title">
    {{ parent_id ? 'Ответить в тред' : 'Создать тред' }}
  </div>
  <b-switch class="form__sage-selector" v-if="parent_id" v-model="isSage">Не поднимать</b-switch>
  <b-field label="Имя">
    <b-input  
      v-model="poster"
      lazy
      custom-class="form__poster-input"
      placeholder="Anonymous"
      icon-right="close-circle"
      icon-right-clickable
      @icon-right-click="onPosterReset"
      :data-is-empty="(poster.length > 0).toString()"
    />
  </b-field>
  <b-field label="Тема">
    <b-input v-model="subject" lazy />
  </b-field>
  <b-field label="Сообщение">
    <b-input v-model="message" max-length="200" type="textarea" ref="message" lazy/>
  </b-field>
  <b-field label="Медиафайлы">
    <div class="form__file-uploader-wrap">
      <b-upload v-model="files" class="file-label" drag-drop multiple>
        <span class="file-cta">
          <b-icon class="file-icon" icon="upload"/>
          <span class="file-label">PNG JPEG WEBM MP4 GIF</span>
        </span>
      </b-upload>
      <span v-if="filesNames.length > 0">
        {{ filesNames.join(', ') }}
      </span>
    </div>
  </b-field>
  <b-button
    type="is-primary"
    v-bind:loading="isLoading"
    @click="defineOutputData(parent_id ? 'reply' : 'thread')"
    expanded>
    {{ parent_id ? 'Ответить' : 'Создать'}}
  </b-button>
</div>
</template>

<script>
import { bus } from '../bus';
import { CLPBRD_ERR } from '../constants/common-error-texts.js';

const config = require('../../config');
const axios  = require('axios');
const formData = require('form-data');

export default {
  name: 'Form',
  props: {
    tag: {
      type: String,
    },
    parent_id: {
      type: [String, Boolean, Number],
      default: false
    },
    message: {
      type: String,
      default: ''
    }
  },
  methods: {
    init: function () {
      this.subject = '';
      this.message = '';
      this.isSage = false;
    },
    getPoster: function() {
      return localStorage.getItem('poster');
    },
    setPoster: function (value) {
      return localStorage.setItem('poster', value);
    },
    onPosterReset: function() {
      this.poster = '';
    },
    onNavigatorPaste: async function (event) {
      try {
        const clipboardItems = await navigator.clipboard.read();
        const textMimeType = clipboardItems[0].types.find((value) =>
          value.startsWith('text/')
        );
        const mediaMimeType = clipboardItems[0].types.find((value) =>
          value.startsWith('image/') || value.startsWith('video/')
        );

        if (clipboardItems.length < 1) {
          this.$buefy.toast.open(CLPBRD_ERR.empty);
        }
        if (clipboardItems.length > 0) {
          if (mediaMimeType) {
            event.preventDefault();

            const blob = await clipboardItems[0].getType(mediaMimeType);
            const file = new File([blob], 'screenshot.png', {
              type: mediaMimeType
            });
            file.uid = String(Math.random());

            this.files.push(file);
          }
          if (!mediaMimeType && !textMimeType) {
            this.$buefy.toast.open(CLPBRD_ERR.mime);
          }
        }
      } catch (error) {
        const errTips =
          error.name === 'NotAllowedError'
            ? CLPBRD_ERR.allow
            : error.name === 'DataError'
            ? CLPBRD_ERR.data
            : CLPBRD_ERR.unknown;
        this.$buefy.toast.open(`${errTips} (${error.message})`, 5);
      }
    },
    onEventPaste: function (event) {
      const clipboardItems = event.clipboardData?.files;

      if (clipboardItems?.length < 1) {
        this.$buefy.toast.open(CLPBRD_ERR.empty);
      }
      if (clipboardItems?.length > 0) {
        const imageType = clipboardItems[0].type;

        const hasTextMimeType = imageType.startsWith('text/');
        const hasMediaMimeType = imageType.startsWith('image/') || imageType.startsWith('video/');

        if (hasMediaMimeType) {
          event.preventDefault();

          const blob = clipboardItems[0];
          const file = new File([blob], 'screenshot.png', {
            type: clipboardItems?.[0].type
          });
          file.uid = String(Math.random());
          this.file = file;
        }
        if (!hasMediaMimeType && !hasTextMimeType) {
          this.$buefy.toast.open(CLPBRD_ERR.mime);
        }
      }
    },
    handlePasteEvent: async function (event) {
      if (navigator.clipboard === 'undefined') {
        this.onEventPaste(event);
      } else {
        this.onNavigatorPaste(event);
      }
    },
    checkIfSupportedMediaFileExtension: function (code, string) {
      if (code === 'image') {
        return (/\.(jpe?g?|png|webp|jfif|gif)/).test(string);
      }
      if (code === 'video') {
        return (/\.(webm|mp4)/).test(string);
      }
      return false;
    },
    sendFile: async function (file) {
      const self = this;
      const uploadData = new formData();
      
      uploadData.append('image', file);

      return axios
        .post(config.filestore_url, uploadData, { 'headers': { 'Content-Type': 'multipart/form-data' }})
        .then((response) => {
          const orig = response.data.original_file;
          const thumb = response.data.thumbnail_file;

          self.message = `${self.message}\n[![](${thumb})](${orig})`;
          self.image = null;

          return file.name;
        })
        .catch((error) => {
          const fileExtension = error.response.data.original_file.match(/(.\w*$)/)[0];
          const fileTypeName = this.checkIfSupportedMediaFileExtension('image', fileExtension)
            ? 'изображения'
            : this.checkIfSupportedMediaFileExtension('video', fileExtension)
            ? 'видео'
            : 'файла';

          self.$buefy.toast.open(`Произошла ошибка при отправке ${fileTypeName}: ${error}`);
          self.image = null;
        });
    },
    handleUploadFile: async function () {
      if (this.files.length > 0) {
        this.isLoading = true;

        return Promise
          .all(this.files.map((file) => this.sendFile(file).then((filename) => { this.filesNames.push(filename); })))
          .then(() => {
            this.files = [];
          })
          .finally(() => {
            this.isLoading = false;
          });
      }
      return Promise.reject(CLPBRD_ERR.empty);
    },
    createReply: function (outputData) {
      return axios.put(`${config.chan_url}/v2/post/${this.parent_id}`, outputData);
    },
    createThread: function (outputData) {
      return axios.post(`${config.chan_url}/v2/post`, outputData);
    },
    defineOutputData: function (type) {
      if (this.message.length < 1) {
        this.$buefy.toast.open('Нельзя отправить пустое сообщение!');
        return;
      }

      this.isLoading = true;

      const self = this;
      const outputData = {};
      const isNewThread = type === 'thread';

      outputData['poster']  = this.poster;
      outputData['subject'] = this.subject;
      outputData['message'] = this.message;
      outputData['tag']     = this.tag;

      if (isNewThread && this.isSage) {
        outputData['sage'] = true;
      }

      const appropriatePromise = isNewThread ? this.createThread : this.createReply;

      appropriatePromise(outputData)
        .then(({ data }) => {
          self.init();
          bus.$emit('form:success', [data]);
          localStorage.setItem('poster', outputData['poster']);
          self.$buefy.toast.open('Отправлено!');
        })
        .catch((error) => {
          self.$buefy.toast.open(`Ошибка: ${error}`);
        })
        .finally(() => {
          self.isLoading = false;
        });
    }
  },
  data: function () {
    return {
      poster: this.getPoster(),
      subject: '',
      isSage: false,
      files: [],
      filesNames: [],
      isLoading: false
    }
  },
  watch: {
    'files': function () {
      this.handleUploadFile();
    }
  }
}
</script>

<style scoped>
.form__title {
    text-align: center;
    font-weight: 700;
    font-size: 1.5rem;
}

.form__sage-selector {
    margin-bottom: 0.75rem;
}

.form__file-uploader-wrap {
    display: grid;
    align-items: center;
    grid-template-columns: max-content auto;
    gap: 0.75em;
}
</style>

<style>
.form__poster-input[data-is-empty="false"] + .icon {
    visibility: hidden;
}
</style>
