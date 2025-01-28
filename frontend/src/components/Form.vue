<template>
<div class="box">
  <h3 v-if="!parent_id">Создать тред</h3>
  <h3 v-if="parent_id">Ответить на:</h3>
  <b-switch v-if="parent_id" v-model="isSage">Не поднимать</b-switch>
  <b-field label="Имя">
    <b-input value="Anonymous" v-model="poster"></b-input>
  </b-field>
  <b-field label="Тема">
    <b-input value="" v-model="subject"></b-input>
  </b-field>
  <b-field label="Сообщение">
    <b-input max-length="200" type="textarea" v-model="message" ref="message"></b-input>
  </b-field>
  <b-field label="Изображение">
    <b-upload v-model="file" class="file-label">
      <span class="file-cta">
	<b-icon class="file-icon" icon="upload"></b-icon>
	<span class="file-label">PNG, JPEG или GIF файл</span>
      </span>
      <span class="file-name" v-if="file">
	{{ file.name }}
      </span>
    </b-upload>
  </b-field>
  <b-button v-bind:loading="isLoading" @click="create()" type="is-primary" expanded>Отправить</b-button>
</div>
</template>

<script>
import { bus } from '../bus';

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
        uploadImage: function() {
            var uploadData = new formData();
            var self = this;

            uploadData.append('image', this.file);

            axios.post(config.filestore_url, uploadData, { 'headers': { 'Content-Type': 'multipart/form-data' }}).then((response) => {
                var orig = response.data.original_file;
                var thumb = response.data.thumbnail_file;

                self.message = self.message + '\n' + `[![](${thumb})](${orig})`;
                self.image = null;
            }).catch((error) => {
                self.$buefy.toast.open(`Произошла ошибка при отправке изображения: ${error}`);
                self.image = null;
            });
        },
        create: function () {
            if (this.message.length == 0) {
                this.$buefy.toast.open('Нельзя отправить пустое сообщение!');

                return;
            }

            this.isLoading = true;

            var self = this;

            var data = {};
            data['poster'] = this.poster;
            data['subject'] = this.subject;
            data['message'] = this.message;
            data['tag'] = this.tag;

            if (this.isSage == true) {
                data['sage'] = true;
            }

            if (this.parent_id) {
                data['parent_id'] = this.parent_id;
            }

            axios.post(config.chan_url + '/post', data).then((response) => {
                self.$buefy.toast.open('Отправлено!');
                self.init();

                self.isLoading = false;

                bus.$emit('form:success', [response.data]);
            }).catch((error) => {
                self.$buefy.toast.open(`Ошибка: ${error}`);
            });
        }
    },
    data: function () {
        return {
            poster: 'Anonymous',
            subject: '',
            isSage: false,
            file: null,
            isLoading: false
        }
    },
    watch: {
        'file': function () {
            this.uploadImage();
        }
    }
}

</script>

<style scoped>
.reply-message {
    margin-left: 20px;
}
</style>
