<template>
<div class="box" @paste="handlePasteEvent">
  <div class="form__title">
    {{ parent_id ? 'Ответить в тред' : 'Создать тред' }}
  </div>
  <b-switch
    class="form__sage-selector"
    v-if="parent_id"
    v-model="isSage"
  >
    Не поднимать
  </b-switch>
  <b-field label="Имя">
    <b-input  
      v-model="poster"
      custom-class="form__poster-input"
      placeholder="Anonymous"
      icon-right="close-circle"
      icon-right-clickable
      @icon-right-click="onPosterReset"
      :data-is-empty="(poster.length > 0).toString()"
    />
  </b-field>
  <b-field label="Тема">
    <b-input
      v-model="subject"
    />
  </b-field>
  <b-field label="Сообщение">
    <b-input
      v-model="message"
      type="textarea"
      ref="message"
    />
  </b-field>
  <b-field label="Медиафайлы">
    <div class="form__file-uploader-wrap">
      <b-upload
        v-model="files"
        v-filesize="filesize"
        class="file-label"
        accept="image/png, image/jpeg, image/gif, video/webm, video/mp4, image/webp"
        drag-drop
        multiple
      >
        <span class="file-cta">
          <b-icon class="file-icon" icon="upload"/>
          <span class="file-label">PNG JPEG WEBP GIF WEBM MP4</span>
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
    },
    filesize: {
      type: String,
      default: config.max_filesize
    }
  },
  methods: {
    init: function () {
      this.subject = '';
      this.message = '';
      this.isSage = false;
    },
    getPoster: function() {
      const poster = localStorage.getItem('poster');

      return (poster === 'undefined' || poster === null) ? config.default_poster : poster;
    },
    setPoster: function (value) {
      localStorage.setItem('poster', value);
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
    sendFile: async function (file) {
      const self = this;

      if (file.size > self.filesize) {
        return Promise.reject('Превышен максимальный размер файла в 25 мегабайт');
      }
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
        });
    },
    handleUploadFile: async function () {
      if (this.files.length > 0) {
        this.isLoading = true;

        const self = this;

        return Promise
          .all(self.files.map((file) => self.sendFile(file).then((filename) => { self.filesNames.push(filename); })))
          .then(() => {
            self.files = [];
          })
          .catch((error) => {
            self.$buefy.toast.open(`Ошибка загрузки файла: ${error}`);
          })
          .finally(() => {
            self.isLoading = false;
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

      outputData['poster']  = self.poster;
      outputData['subject'] = self.subject;
      outputData['message'] = self.message;
      outputData['tag']     = self.tag;

      if (!isNewThread && self.isSage) {
        outputData['sage'] = true;
      }
      const appropriatePromise = isNewThread ? self.createThread : self.createReply;

      appropriatePromise(outputData)
        .then(({ data }) => {
          self.init();
          bus.$emit('form:success', [data]);
          self.setPoster(outputData['poster']);
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
  directives: {
    filesize: ((el, binding) => {
      el.querySelector('input').size = binding.value;
    })
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
