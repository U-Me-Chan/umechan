<template>
<div class="box" @paste="handlePasteEvent">
  <div class="form__title">
    {{ parent_id ? 'Ответить в тред' : 'Создать тред' }}
  </div>
  <label v-if="parent_id" class="form__sage-selector checkbox">
    <input type="checkbox" v-model="isSage" />
    Не поднимать
  </label>

  <div class="field">
    <label class="label">Имя</label>
    <div class="control">
      <div class="input-with-icon">
        <input
          class="input form__poster-input"
          v-model="poster"
          placeholder="Anonymous"
          :data-is-empty="poster.length > 0"
        />
        <span v-if="poster.length > 0" class="icon-reset" @click="onPosterReset">✕</span>
      </div>
    </div>
  </div>

  <div class="field">
    <label class="label">Тема</label>
    <div class="control">
      <input
        class="input"
        v-model="subject"
      />
    </div>
  </div>

  <div class="field">
    <label class="label">Сообщение</label>
    <div class="control">
      <textarea
        class="textarea"
        v-model="message"
        ref="messageTextarea"
      ></textarea>
    </div>
  </div>

  <div class="field">
    <label class="label">Медиафайлы</label>
    <div class="form__file-uploader-wrap">
      <label class="file-label file-cta">
        <input
          class="file-input"
          type="file"
          :accept="supportedMimetypesAsString"
          multiple
          @change="onFileSelect"
        />
        <span class="file-cta">
          <span class="file-icon">📤</span>
          <span class="file-label-text">Выберите файл</span>
        </span>
      </label>
      <span v-if="filesNames.length > 0">
        <ul>
          <li class="uploaded-filename" v-for="filename in filesNames" :key="filename">{{filename}}</li>
        </ul>
      </span>
    </div>
  </div>

  <div class="control">
    <button
      class="button is-primary"
      :class="{ 'is-loading': isLoading }"
      :disabled="isLoading"
      @click="defineOutputData(parent_id ? 'reply' : 'thread')"
      style="width: 100%;">
      {{ parent_id ? 'Ответить' : 'Создать'}}
    </button>
  </div>
</div>
</template>

<script>
import { ref, watch, onBeforeUnmount } from 'vue';
import { bus } from '../bus';
import { createReply, createThread } from '../api/posts';
import { uploadFile, getSupportedMimetypes } from '../api/files';
import { CLPBRD_ERR } from '../constants/common-error-texts';
import { convertBytesToMegabytes } from '../utils/filesize_formatter';
import { getPosterName, setPosterName, setPostPassword } from '../utils/storage';
import config from '../../config';

const getFormData = () => {
  if (typeof window !== 'undefined' && window.FormData) {
    return window.FormData;
  }
  // Simple FormData polyfill for Node.js testing if needed
  return class FormData {
    append() {}
  };
};

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
  setup(props) {
    const poster = ref(getPosterName());
    const subject = ref('');
    const isSage = ref(false);
    const files = ref([]);
    const filesNames = ref([]);
    const isLoading = ref(false);
    const supportedMimetypesAsString = ref('Ошибка загрузки поддерживаемых типов файлов');
    const messageTextarea = ref(null);

    // Load supported mimetypes on mount
    getSupportedMimetypes().then((response) => {
      supportedMimetypesAsString.value = response.data.mimetypes.join(', ');
    });

    const formInit = () => {
      subject.value = '';
      message.value = '';
      isSage.value = false;
      filesNames.value = [];
    };

    const onPosterReset = () => {
      poster.value = '';
    };

    const onNavigatorPaste = async (event) => {
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
          files.value.push(file);
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
        await onNavigatorPaste(event);
      }
    };

    const message = ref(props.message);

    const sendFile = async function (file) {
      if (file.size > config.max_filesize) {
        return Promise.reject(`Превышен максимальный размер файла в ${convertBytesToMegabytes(config.max_filesize)} Мбайт`);
      }
      const uploadData = new getFormData();
      uploadData.append('image', file);

      return uploadFile(uploadData)
        .then((response) => {
          const orig = response.data.original_file;
          const thumb = response.data.thumbnail_file;
          message.value = `${message.value}\n[![](${thumb})](${orig})`;
          return file.name;
        });
    };

    const handleUploadFile = async function () {
      if (files.value.length > 0) {
        isLoading.value = true;

        return Promise
          .all(files.value.map((file) => sendFile(file).then((filename) => { filesNames.value.push(filename); })))
          .then(() => {
            files.value = [];
          })
          .catch((error) => {
            showToast(`Ошибка загрузки файла: ${error}`);
          })
          .finally(() => {
            isLoading.value = false;
          });
      }
      return Promise.reject(CLPBRD_ERR.empty);
    };

    const onFileSelect = (event) => {
      const selectedFiles = Array.from(event.target.files);
      files.value = selectedFiles;
    };

    const handleFileChange = () => {
      // Deprecated: file selection is now handled in onFileSelect
    };

    const createReplyApi = function (outputData) {
      return createReply(outputData, props.parent_id);
    };

    const createThreadApi = function (outputData) {
      return createThread(outputData);
    };

    const defineOutputData = function (type) {
      if (message.value.length < 1) {
        showToast('Нельзя отправить пустое сообщение!');
        return;
      }
      isLoading.value = true;

      const outputData = {};
      const isNewThread = type === 'thread';

      outputData['poster']  = poster.value;
      outputData['subject'] = subject.value;
      outputData['message'] = message.value;
      outputData['tag']     = props.tag;

      if (!isNewThread && isSage.value) {
        outputData['sage'] = true;
      }
      const appropriatePromise = isNewThread ? createThreadApi : createReplyApi;

      appropriatePromise(outputData)
        .then(({ data }) => {
          setPostPassword(data.payload.post_id.value, data.payload.password);
          formInit();
          bus.$emit('form:success', [data]);
          setPosterName(outputData['poster']);
          showToast('Отправлено!');
        })
        .catch((error) => {
          showToast(`Ошибка: ${error.response.data.error.message}`);
        })
        .finally(() => {
          isLoading.value = false;
        });
    };

    const showToast = (message) => {
      alert(message);
    };

    const filesWatcher = watch(files, () => {
      handleUploadFile();
    });

    onBeforeUnmount(() => {
      filesWatcher();
    });

    return {
      poster,
      subject,
      isSage,
      files,
      filesNames,
      isLoading,
      supportedMimetypesAsString,
      messageTextarea,
      message,
      formInit,
      onPosterReset,
      handlePasteEvent,
      handleFileChange,
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

.form__poster-input {
    width: 100%;
    padding: 8px;
    border: 1px solid var(--color-input-border);
    background-color: var(--color-input-bg);
    color: var(--color-input-text);
    border-radius: 4px;
}

.input-with-icon {
    position: relative;
}

.icon-reset {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--color-text-secondary);
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
