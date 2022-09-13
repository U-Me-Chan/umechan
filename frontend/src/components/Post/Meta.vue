<template>
<div class="post-meta">
  <Poster :poster="poster" :isVerify="isVerify"/>
  <Subject v-if="subject" :subject="subject"/>
  <b-tag>{{ datetime }}</b-tag>
  <b-tag v-if="board">/{{board.tag}}/</b-tag>
  <b-tag>№{{ id }}</b-tag>
  <a v-if="!parentId" :href="'/thread/' + id">
    <b-button type="is-text" size="is-small" @click="selectThread(id, $event)">Открыть</b-button>
  </a>

  <b-button type="is-text" size="is-small" @click="isFormVisible = !isFormVisible">Ответить</b-button>
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
    id: Number,
    poster: String,
    subject: String,
    datetime: String,
    isVerify: Boolean,
    parentId: {
      type: [Number, Boolean],
      default: false
    },
    board: Object
  },
  methods: {
    selectThread: function (id, event) {
      event.preventDefault();

      this.$router.push('/thread/' + id);
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
