<template>
<div class="post-meta">
  <Poster :poster="poster" :isVerify="isVerify"/>
  <Subject v-if="subject" :subject="subject"/>
  <b-tag>{{ datetime }}</b-tag>
  <b-tag v-if="board">/{{board.tag}}/</b-tag>
  <b-tag>№{{ id }}</b-tag>
  <b-tag v-if="isSticky">📌</b-tag>
  <b-tag v-if="isBumpLimit">🌕</b-tag>
  <b-tag v-if="repliesCount">∑{{repliesCount}}</b-tag>
  
  <a v-if="isShowButtons && !parentId" :href="'/thread/' + id">
    <b-button type="is-text" size="is-small" @click="selectThread(id, $event)">➜</b-button>
  </a>
  
  <b-button v-if="isShowButtons" type="is-text" size="is-small" @click="isFormVisible = !isFormVisible">✍</b-button>
  <b-modal v-model="isFormVisible">
    <Form v-if="isFormVisible"
          :parent_id="!parentId ? id : parentId"
          :message="`>>${id}\n`"/>
  </b-modal>

  <b-dropdown v-if="isShowAdminButtons && parentId">
    <template #trigger>
      <b-button type="is-text" size="is-small" icon-right="menu-down">
        🚔
      </b-button>
    </template>
    <b-dropdown-item @click="deletePost(id, 'Неприлично! 😡')">Неприлично! 😡</b-dropdown-item>
    <b-dropdown-item @click="deletePost(id, 'Незаконно! 🚔🔒')">Незаконно! 🚔🔒</b-dropdown-item>
    <b-dropdown-item @click="deletePost(id, 'Оффтопик! 🙅')">Оффтопик! 🙅</b-dropdown-item>
  </b-dropdown>
</div>
</template>

<script>
import axios from 'axios'
import { bus } from '../../bus'
import Poster from './Poster.vue'
import Subject from './Subject.vue'
import Form from '../Form.vue'

const config = require('../../../config')

export default {
  name: 'Meta',
  components: {
    Poster, Subject, Form
  },
  props: {
    id: {
      type: Number,
      default: 0
    },
    poster: String,
    subject: String,
    datetime: String,
    isVerify: Boolean,
    isSticky: Boolean,
    isBumpLimit: Boolean,
    repliesCount: {
      type: [Number, Boolean],
      default: false
    },
    parentId: {
      type: [Number, Boolean],
      default: false
    },
    board: Object,
    isShowButtons: {
      type: Boolean,
      default: true
    }
  },
  methods: {
    selectThread: function (id, event) {
      event.preventDefault();

      this.$router.push('/thread/' + id);
    },
    deletePost: function (id, reason) {
      var key = ''

      if (this.$cookie.get('admin_key') !== null) {
        key = this.$cookie.get('admin_key')
      } else {
        key = prompt('Admin key', [''])
      }

      var self = this

      axios.post(
        config.chan_url + '/_/v2/post/' + id,
        {
          reason: reason
        },
        {
          headers: {
            'Key': key,
            'Content-type': 'application/json;charset=utf-8',
            'Accept': 'application/json;charset=utf-8'
          }
        }
      ).then(() => {
        bus.$emit('thread:updated')
        bus.$emit('board:updated')
      }).catch((error) => {
        self.$buefy.toast.open('Ошибка при удалении')
        console.debug(error)
      })
    }
  },
  computed: {
    isShowAdminButtons: function () {
      return this.$cookie.get('admin_key') !== null ? true : false;
    }
  },
  data: function () {
    return {
      isFormVisible: false
    }
  },
  mounted: function () {
    bus.$on('form:success', () => this.isFormVisible = false);
  }
}
</script>
