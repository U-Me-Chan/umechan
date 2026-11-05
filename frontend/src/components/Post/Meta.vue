<template>
<div class="post-meta">
  <PosterComponent :poster="poster" :isVerify="isVerify"/>
  <SubjectComponent v-if="subject" :subject="subject"/>
  <span class="post-datetime">{{ datetime }}</span>
  <span v-if="board" class="board-tag">/{{board.tag}}/</span>
  <span class="post-id">#{{ id }}</span>
  <span v-if="isSticky" class="infotag">📌</span>
  <span v-if="isBumpLimit" class="infotag">🌕</span>
  <span v-if="repliesCount" class="infotag">∑{{repliesCount}}</span>
  <span v-if="isBlocked" class="infotag">🔒</span>

  <div v-if="isAdminKeyExist && parentId" class="admin-dropdown">
    <button class="btn-small" @click="toggleDropdown">🚔</button>
    <div v-if="dropdownOpen" class="dropdown-menu">
      <a class="dropdown-item" @click="erasePost(id, 'Неприлично! 😡')">Неприлично! 😡</a>
      <a class="dropdown-item" @click="erasePost(id, 'Незаконно! 🚔')">Незаконно! 🚔</a>
      <a class="dropdown-item" @click="erasePost(id, 'Оффтопик! 🙅')">Оффтопик! 🙅</a>
    </div>
  </div>

  <a v-if="isShowButtons && !parentId" :href="'/thread/' + id">
    <button class="btn-small" @click="selectThread(id, $event)">➜</button>
  </a>

  <button v-if="isPostPasswordExist" class="btn-small" @click="deletePost(id)">🗑</button>

  <button v-if="isShowButtons && !isBlocked" class="btn-small" @click="isFormVisible = !isFormVisible">✍</button>
  <div v-if="isFormVisible" class="modal-overlay" @click.self="isFormVisible = false">
    <FormComponent
      :parent_id="!parentId ? id : parentId"
      :message="`>>${id}\n`"
    />
  </div>
</div>
</template>

<script>
import { ref, computed, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { deletePost, erasePost } from '../../api/posts';
import { bus } from '../../bus';
import PosterComponent from './Poster.vue';
import SubjectComponent from './Subject.vue';
import FormComponent from '../Form.vue';
import { getPostPassword, getAdminKey } from '../../utils/storage';

export default {
  name: 'Meta',
  components: {
    PosterComponent, SubjectComponent, FormComponent
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
    },
    isBlocked: {
      type: Boolean,
      default: false
    }
  },
  emits: ['post-deleted'],
  setup(props, { emit }) {
    const router = useRouter();
    const isFormVisible = ref(false);
    const dropdownOpen = ref(false);

    const isAdminKeyExist = computed(() => getAdminKey() !== null);
    const isPostPasswordExist = computed(() => getPostPassword(props.id) !== null);

    const selectThread = (id, event) => {
      event.preventDefault();
      router.push('/thread/' + id);
    };

    const toggleDropdown = () => {
      dropdownOpen.value = !dropdownOpen.value;
    };

    const deletePostHandler = (id) => {
      if (!confirm('Точно удалить?')) {
        return;
      }

      const password = localStorage.getItem(id);

      deletePost(id, password).then(() => {
        bus.$emit('thread:updated');
        bus.$emit('board:updated');
        emit('post-deleted', id);
      }).catch((error) => {
        showToast('Ошибка при удалении');
        console.error(error);
      });
    };

    const erasePostHandler = (id, reason) => {
      let key = '';

      if (!isAdminKeyExist.value) {
        key = prompt('Admin key', ['']);
      } else {
        key = getAdminKey();
      }

      erasePost(id, reason, key).then(() => {
        bus.$emit('thread:updated');
        bus.$emit('board:updated');
      }).catch((error) => {
        showToast('Ошибка при удалении');
        console.error(error);
      });
    };

    const showToast = (message) => {
      alert(message);
    };

    onBeforeUnmount(() => {
      dropdownOpen.value = false;
    });

    return {
      isFormVisible,
      dropdownOpen,
      isAdminKeyExist,
      isPostPasswordExist,
      selectThread,
      toggleDropdown,
      deletePost: deletePostHandler,
      erasePost: erasePostHandler
    };
  }
}
</script>

<style scoped>
.infotag {
    font-size: 12px;
}

.post-datetime {
    font-size: 12px;
    margin: 0 4px;
}

.board-tag {
    font-size: 12px;
    margin: 0 4px;
}

.post-id {
    font-size: 12px;
    margin: 0 4px;
}

.btn-small {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 14px;
    padding: 2px 4px;
}

.admin-dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: var(--color-card-bg);
    border: 1px solid var(--color-border);
    border-radius: 4px;
    box-shadow: 0 2px 8px var(--color-shadow);
    z-index: 100;
    min-width: 200px;
}

.dropdown-item {
    display: block;
    padding: 8px 12px;
    cursor: pointer;
    color: var(--color-text);
    text-decoration: none;
}

.dropdown-item:hover {
    background-color: var(--color-tag-bg);
}
</style>
