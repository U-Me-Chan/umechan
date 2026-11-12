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
      <b-input v-model="subject" />
    </b-field>
    <b-field label="Сообщение">
      <b-input
        v-model="message"
        type="textarea"
        ref="messageInput"
      />
    </b-field>
    <b-field label="Медиафайлы">
      <div class="form__file-uploader-wrap">
        <b-upload
          :modelValue="files"
          class="file-label"
          :accept="supportedMimetypesAsString"
          drag-drop
          multiple
          @update:modelValue="files = $event"
        >
          <span class="file-cta">
            <b-icon class="file-icon" icon="upload"/>
            <span class="file-label">Выберите файл</span>
          </span>
        </b-upload>
        <span v-if="filesNames.length > 0">
          <ul>
            <li class="uploaded-filename" v-for="filename in filesNames" :key="filename">{{ filename }}</li>
          </ul>
        </span>
      </div>
    </b-field>
    <b-button
      type="is-primary"
      :loading="isLoading"
      @click="defineOutputData(parent_id ? 'reply' : 'thread')"
      expanded>
      {{ parent_id ? 'Ответить' : 'Создать'}}
    </b-button>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import axios from 'axios';
import FormData from 'form-data';
import config from '../../config';
import { bus } from '../bus';
import { createReply, createThread } from '../api/posts';
import { uploadFile, getSupportedMimetypes } from '../api/files';
import { CLPBRD_ERR } from '../constants/common-error-texts';
import { convertBytesToMegabytes } from '../utils/filesize_formatter';
import { getPosterName, setPosterName, setPostPassword } from '../utils/storage';
import { showToast } from '../utils/toast';

export default {
  name: 'Form',
  props: {
    tag: { type: String },
    parent_id: { type: [String, Boolean, Number], default: false },
    message: { type: String, default: '' }
  },
  setup(props) {
    const poster = ref(getPosterName());
    const subject = ref('');
    const isSage = ref(false);
    const files = ref([]);
    const filesNames = ref([]);
    const isLoading = ref(false);
    const supportedMimetypesAsString = ref('Ошибка загрузки поддерживаемых типов файлов');
    const messageInput = ref(null);
    const localMessage = ref(props.message);

    getSupportedMimetypes().then((response) => {
      supportedMimetypesAsString.value = response.data.mimetypes.join(', ');
    });

    const init = function () {
      subject.value = '';
      isSage.value = false;
    };

    const onPosterReset = function() {
      poster.value = '';
    };

    const onNavigatorPaste = async function (event) {
      try {
        const clipboardItems = await navigator.clipboard.read();
        const textMimeType = clipboardItems[0].types.find((value) =>
          value.startsWith('text/')
        );
        const mediaMimeType = clipboardItems[0].types.find((value) =>
          value.startsWith('image/') || value.startsWith('video/')
        );

        if (clipboardItems.length < 1) {
          showToast(CLPBRD_ERR.empty);
        }
        if (clipboardItems.length > 0) {
          if (mediaMimeType) {
            event.preventDefault();

            const blob = await clipboardItems[0].getType(mediaMimeType);
            const file = new File([blob], 'screenshot.png', {
              type: mediaMimeType
            });
            file.uid = String(Math.random());

            files.value.push(file);
          }
          if (!mediaMimeType && !textMimeType) {
            showToast(CLPBRD_ERR.mime);
          }
        }
      } catch (error) {
        const errTips =
          error.name === 'NotAllowedError'
            ? CLPBRD_ERR.allow
            : error.name === 'DataError'
            ? CLPBRD_ERR.data
            : CLPBRD_ERR.unknown;
        showToast(`${errTips} (${error.message})`, 5);
      }
    };

    const onEventPaste = function (event) {
      const clipboardItems = event.clipboardData?.files;

      if (clipboardItems?.length < 1) {
        showToast(CLPBRD_ERR.empty);
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
        }
        if (!hasMediaMimeType && !hasTextMimeType) {
          showToast(CLPBRD_ERR.mime);
        }
      }
    };

    const handlePasteEvent = async function (event) {
      if (navigator.clipboard === 'undefined') {
        onEventPaste(event);
      } else {
        onNavigatorPaste(event);
      }
    };

    const sendFile = async function (file) {
      if (file.size > config.max_filesize) {
        return Promise.reject(`Превышен максимальный размер файла в ${convertBytesToMegabytes(config.max_filesize)} Мбайт`);
      }
      const uploadData = new FormData();

      uploadData.append('image', file);

      return uploadFile(uploadData)
        .then((response) => {
          return file.name;
        });
    };

    const handleUploadFile = async function () {
      if (files.value.length === 0) {
        return;
      }

      isLoading.value = true;

      const filesToUpload = [...files.value];

      return Promise
        .all(filesToUpload.map((file) => sendFile(file).then((filename) => { filesNames.value.push(filename); })))
        .then(() => {
          files.value = [];
        })
        .catch((error) => {
          showToast(`Ошибка загрузки файла: ${error}`, 5);
        })
        .finally(() => {
          isLoading.value = false;
        });
    };

    const createReplyFn = function (outputData) {
      return createReply(outputData, props.parent_id);
    };

    const createThreadFn = function (outputData) {
      return createThread(outputData);
    };

    const defineOutputData = function (type) {
      if (localMessage.value.length < 1) {
        showToast('Нельзя отправить пустое сообщение!');
        return;
      }
      isLoading.value = true;

      const outputData = {};
      const isNewThread = type === 'thread';

      outputData['poster']  = poster.value;
      outputData['subject'] = subject.value;
      outputData['message'] = localMessage.value;
      outputData['tag']     = props.tag;

      if (!isNewThread && isSage.value) {
        outputData['sage'] = true;
      }
      const appropriatePromise = isNewThread ? createThreadFn : createReplyFn;

      appropriatePromise(outputData)
        .then(({ data }) => {
          setPostPassword(data.payload.post_id.value, data.payload.password);
          init();
          bus.$emit('form:success', [data]);
          setPosterName(outputData['poster']);
          showToast('Отправлено!');
        })
        .catch((error) => {
          showToast(`Ошибка: ${error.response.data.error.message}`, 5);
        })
        .finally(() => {
          isLoading.value = false;
        });
    };

    watch(files, () => {
      handleUploadFile();
    });

    return {
      poster,
      subject,
      isSage,
      files,
      filesNames,
      isLoading,
      supportedMimetypesAsString,
      messageInput,
      message: localMessage,
      init,
      onPosterReset,
      onNavigatorPaste,
      onEventPaste,
      handlePasteEvent,
      sendFile,
      handleUploadFile,
      createReply: createReplyFn,
      createThread: createThreadFn,
      defineOutputData
    };
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

.form__file-uploader-wrap {
    display: flex !important;
    flex-direction: column !important;
}

.uploaded-filename {
    list-style-type: none;
}

.theme-dark .form .box {
    background-color: var(--color-surface);
    color: var(--color-text);
}
</style>
