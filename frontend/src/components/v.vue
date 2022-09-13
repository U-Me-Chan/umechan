<template>
<div class="card" :id="id" :ref="id">
  <b-tag v-if="isVerify" size="is-small" type="is-success"><b-tooltip label="Личность этого автора удостоверена">{{ poster }}</b-tooltip></b-tag>
  <b-tag v-if="!isVerify" size="is-small" type="is-light">{{ poster }}</b-tag>
  <b-tag size="is-small" type="is-info is-light" v-if="subject">{{subject}}</b-tag>
  <b-tag>{{ datetime }}</b-tag>
  <b-tag v-if="isThread && repliesCount < 500" size="is-small" type="is-light">Ответов: {{ repliesCount }}</b-tag>
  <b-tag v-if="isThread && repliesCount >= 500" size="is-small" type="is-light is-danger">Тред не поднимется</b-tag>
  <b-tag size="is-small" type="is-light">#{{ !parentId ? id : parentId + '/' + id }}</b-tag>
  <b-button type="is-text" size="is-small" @click="selectThread(id)">Открыть</b-button>
  <b-button type="is-text" size="is-small" @click="isFormVisible = !isFormVisible">Ответить</b-button>
  <b-modal v-model="isFormVisible">
    <Form v-if="isFormVisible"
          v-bind:parent_id="!parentId ? id : parentId"
          v-bind:replyMessage="filterMessage"
          v-bind:message="`>>${id}\n`"/>
  </b-modal>

  <div class="media-box">
    <p class="image-box" v-for="image in images" :key="image.link">
      <a target="_blank" :href="image.link">
	<img :src="image.preview">
      </a>
    </p>

    <p class="youtube-box" v-for="youtube in youtubes" :key="youtube.link">
      <a target="_blank" :href="youtube.link"><img :src="youtube.preview"></a>
    </p>
  </div>

  <div v-if="isLongPost" class="post-message">
    <vue-markdown v-if="!isPostFull" :typographer=true
                  :html=true
                  :toc=false
                  :source=headMessage
                  :prerender="prerender"></vue-markdown>

    <b-collapse :open="false" position="is-bottom" v-bind:aria-id="id">
      <template #trigger>
        <div v-bind:aria-controls="id" @click="isPostFull = !isPostFull">
          <b-tag class="post-toggle" size="is-small">{{ !isPostFull ? 'Показать полностью' : 'Скрыть полную версию' }}</b-tag>
        </div>
      </template>

      <vue-markdown :typographer=true
                    :html=true
                    :toc=false
                    :source=filterMessage
                    :prerender="prerender"></vue-markdown>
    </b-collapse>
  </div>

  <div v-if="!isLongPost" class="post-message">
    <vue-markdown :typographer=true
                  :html=true
                  :toc=false
                  :source=filterMessage
                  :prerender="prerender"></vue-markdown>
  </div>
</div>
</template>

<script>
import VueMarkdown from 'vue-markdown'
import Form from './Form.vue'
import { bus } from '../bus'
import Prism from  'prismjs'
import 'prismjs/components/prism-bash'
import 'prismjs/components/prism-c'
import 'prismjs/components/prism-clike'
import 'prismjs/components/prism-cpp'
import 'prismjs/components/prism-csharp'
import 'prismjs/components/prism-csv'
import 'prismjs/components/prism-diff'
import 'prismjs/components/prism-docker'
import 'prismjs/components/prism-git'
import 'prismjs/components/prism-go'
import 'prismjs/components/prism-json'
import 'prismjs/components/prism-lisp'
import 'prismjs/components/prism-nginx'
import 'prismjs/components/prism-makefile'
import 'prismjs/components/prism-perl'
import 'prismjs/components/prism-php'
import 'prismjs/components/prism-php-extras'
import 'prismjs/components/prism-python'
import 'prismjs/components/prism-rust'
import 'prismjs/components/prism-sql'
import 'prismjs/components/prism-typescript'
import 'prismjs/components/prism-verilog'
import 'prismjs/components/prism-vim'
import 'prismjs/components/prism-zig'
import 'prismjs/components/prism-markup-templating'
import 'prismjs/themes/prism.css'

export default {
    components: {
        VueMarkdown, Form
    },
    name: 'Post',
    data: function () {
        return {
            isFormVisible: false,
            isPostFull: false
        }
    },
    props: {
        id: [String,Number],
        poster: String,
        isVerify: {
            type: Boolean,
            default: false
        },
        subject: {
            type: String,
            default: '...'
        },
        message: {
            type: String,
            default: ''
        },
        parentId: String,
        datetime: String,
        isThread: {
            type: Boolean,
            default: false,
        },
        isFeedParent: {
            type: Boolean,
            default: false
        },
        repliesCount: {
            type: Number,
            default: 0
        },
        images: Array,
        youtubes: Array,
	replies: Array
    },
    mounted: function () {
        bus.$on('form:success', () => this.isFormVisible = false);
	Prism.highlightAll();
    },
    computed: {
        filterMessage: function () {
            return this.message;
        },
        isLongPost: function () {
            return this.message.length > 600 ? true : false;
        },
        headMessage: function () {
            return this.filterMessage.slice(0, 600);
        }
    },
    methods: {
        selectThread: function (id) {
            if (this.parentId) {
                this.$router.push('/thread/' + this.parentId + '#' + id);
            } else {
                this.$router.push('/thread/' + id);
            }
        },
        prerender: function (message) {
            const reply = />>\d{1,10}/g;
            //const image = /(?!\\!\[[a-z]+\]\()(?<!['|"])((?<twilink>https:\/\/pbs\.twimg\.com\/media\/[a-z0-9?=&]+)|(?<link>https?:\/\/[a-z.\0-9-_]+\.(?<ext>jpg|jpeg?|gif|png)(?<params>\?[a-z=&0-9]+)?))(?<!['|"])$(?!\))/gmi;
            const audio = /(https?):\/\/[a-z./0-9-_]+(\.(ogg|mp3)$)/gmi;
            //const youtube = /(https:\/\/www\.youtube\.com\/watch\?v=([0-9a-z_-]+)|https:\/\/youtu\.be\/([0-9a-z_-]+))/mi;

            return message.replace(/<.+>/gmi, () => {
                return '';
            }).replace(reply, match => {
                return `<a href='#${match.slice('>>'.length)}'>${match}</a>`;
            }).replace(audio, match => {
                return `<br><audio controls=true src='${match}'><br>`;
            });
        }
    }
}
</script>

<style>
.post-message {
    margin: 30px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    font-size: 14px;
}


.media-box {
    margin: 30px;
    display: flex;
    flex-wrap: wrap;
}

.image-box {
    margin: 5px;
}

.image-box img {
    border: 2px solid #ede7fb;
}

.youtube-box img {
    border: 2px solid red;
}

.post-active {
    border: 2px solid #888;
}

img {
    margin-right: 30%;
}

blockquote {
    margin-left: 0px;
    border-left: 5px solid #ddd;
    padding-left: 2px;
    color: #083;
}

img {
    max-width: 240px;
    width: auto;
}

pre {
    background-color: #f5f2f0;
}

.tag {
    margin-right: 10px;
}

.post-toggle {
    margin-top: 10px;
}

.card {
    padding: 10px;
    margin: 5px;
}
</style>
