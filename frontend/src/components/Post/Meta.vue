<template>
  <div class="post-meta">
    <Poster :poster="poster" :isVerify="isVerify"/>
    <Subject v-if="subject" :subject="subject"/>
    <b-tag>{{ datetime }}</b-tag>
    <b-tag v-if="board">/{{ board.tag }}/</b-tag>
    <b-tag>#{{ id }}</b-tag>
    <b-tag class="infotag" v-if="isSticky">📌</b-tag>
    <b-tag class="infotag" v-if="isBumpLimit">🌕</b-tag>
    <b-tag class="infotag" v-if="repliesCount">∑{{ repliesCount }}</b-tag>
    <b-tag class="infotag" v-if="isBlocked">🔒</b-tag>
    <b-dropdown v-if="isAdminKeyExist && parentId">
      <template #trigger>
        <b-button type="is-text" size="is-small" icon-right="menu-down">
          🚔
        </b-button>
      </template>
      <b-dropdown-item @click="erasePost(id, 'Неприлично! 😡')">Неприлично! 😡</b-dropdown-item>
      <b-dropdown-item @click="erasePost(id, 'Незаконно! 🚔')">Незаконно! 🚔</b-dropdown-item>
      <b-dropdown-item @click="erasePost(id, 'Оффтопик! 🙅')">Оффтопик! 🙅</b-dropdown-item>
    </b-dropdown>

    <a v-if="isShowButtons && !parentId" :href="'/thread/' + id">
      <b-button type="is-text" size="is-small" @click="selectThread(id, $event)">➜</b-button>
    </a>

    <b-button v-if="isPostPasswordExist" type="is-text" size="is-small" @click="deletePost(id)">🗑</b-button>

    <b-button v-if="isShowButtons && !isBlocked" type="is-text" size="is-small" @click="isFormVisible = !isFormVisible">✍</b-button>
    <b-modal v-model="isFormVisible">
      <Form v-if="isFormVisible"
            :parent_id="!parentId ? id : parentId"
            :message="`>>${id}\n`"/>
    </b-modal>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { deletePost, erasePost } from '../../api/posts';
import { bus } from '../../bus';
import Poster from './Poster.vue';
import Subject from './Subject.vue';
import Form from '../Form.vue';
import { getPostPassword, getAdminKey } from '../../utils/storage';
import { showToast } from '../../utils/toast';

export default {
  name: 'Meta',
  components: {
    Poster, Subject, Form
  },
  props: {
    id: { type: Number, default: 0 },
    poster: String,
    subject: String,
    datetime: String,
    isVerify: Boolean,
    isSticky: Boolean,
    isBumpLimit: Boolean,
    repliesCount: { type: [Number, Boolean], default: false },
    parentId: { type: [Number, Boolean], default: false },
    board: Object,
    isShowButtons: { type: Boolean, default: true },
    isBlocked: { type: Boolean, default: false }
  },
  setup(props) {
    const router = useRouter();
    const isFormVisible = ref(false);

    const selectThread = function (id, event) {
      event.preventDefault();
      router.push('/thread/' + id);
    };

    const deletePostFn = function (id) {
      if (!confirm('Точно удалить?')) {
        return;
      }

      const password = localStorage.getItem(String(id));

      deletePost(id, password).then(() => {
        bus.$emit('thread:updated');
        bus.$emit('board:updated');
      }).catch((error) => {
        showToast({ message: 'Ошибка при удалении', type: 'is-danger' });
        console.error(error);
      });
    };

    const erasePostFn = function (id, reason) {
      var key = '';

      if (!isAdminKeyExist.value) {
        key = prompt('Admin key', ['']);
      } else {
        key = getAdminKey();
      }

      erasePost(id, reason, key).then(() => {
        bus.$emit('thread:updated');
        bus.$emit('board:updated');
      }).catch((error) => {
        showToast({ message: 'Ошибка при удалении', type: 'is-danger' });
        console.error(error);
      });
    };

    const isAdminKeyExist = computed(() => getAdminKey() !== null);
    const isPostPasswordExist = computed(() => getPostPassword(props.id) !== null);

    bus.$on('form:success', () => { isFormVisible.value = false; });

    return {
      isFormVisible,
      selectThread,
      deletePost: deletePostFn,
      erasePost: erasePostFn,
      isAdminKeyExist,
      isPostPasswordExist
    };
  }
}
</script>

<style scoped>
.infotag {
    font-size: 12px;
}
</style>
