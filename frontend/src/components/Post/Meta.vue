<template>
<div class="post-meta">
  <Poster :poster="poster" :isVerify="isVerify"/>
  <Subject v-if="subject" :subject="subject"/>
  <b-tag>{{ datetime }}</b-tag>
  <b-tag v-if="board">/{{board.tag}}/</b-tag>
  <b-tag>№{{ id }}</b-tag>
  
  <a v-if="isShowButtons && !parentId" :href="'/thread/' + id">
    <b-button type="is-text" size="is-small" @click="selectThread(id, $event)">Открыть</b-button>
  </a>

  <b-button v-if="isShowButtons" type="is-text" size="is-small" @click="isFormVisible = !isFormVisible">Ответить</b-button>
  <b-button v-if="isAdmin" type="is-text" size="is-small" @click="deletePost(id, $event)">Удалить</b-button>
  <b-modal v-model="isFormVisible">
    <Form v-if="isFormVisible"
          :parent_id="!parentId ? id : parentId"
          :message="`>>${id}\n`"/>
  </b-modal>
</div>
</template>

<script>
import { bus } from '../../bus'
import Poster from './Poster.vue'
import Subject from './Subject.vue'
import Form from '../Form.vue'

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
    deletePost: function (id, event) {
      event.preventDefault();

      this.$router.push('/admin/delete-post/' + id);
    }
  },
  computed: {
    isAdmin: function () {
      return this.$cookie.get('admin_key') ? true : false; // FIXME: нужен единый источник подобной информации
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
