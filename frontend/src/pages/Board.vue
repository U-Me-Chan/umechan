<template>
<div class="board">
  <h1>{{ tag }}</h1>

  <div v-if="!isMetaBoard">
    <center><button class="btn-text" @click="isFormVisible = !isFormVisible">Создать тред</button></center>
    <div v-if="isFormVisible" class="modal-overlay" @click.self="isFormVisible = false">
      <Form v-if="isFormVisible" :tag="tag"/>
    </div>
  </div>
  <hr v-if="isContentExist">

  <div v-if="isContentExist" class="board-paginator">
    <PaginationComponent
      :total="count"
      :current="current"
      :per-page="perPage"
      @change="handlePageChange"
    />
  </div>

  <div class="board-threads">
    <section class="threads" v-for="post in threads" :key="post.id">
      <hr v-if="isContentExist">
      <ThreadComponent
        :board="post.board"
        :id="post.id"
        :poster="post.poster"
        :isVerify="post.is_verify"
        :subject="post.subject"
        :parentId="post.parent_id"
        :datetime="post.datetime"
        :message="post.truncated_message"
        :images="post.media.images"
        :youtubes="post.media.youtubes"
        :videos="post.media.videos"
        :replies="post.replies"
        :repliesCount="post.replies_count"
        :isBumpLimitReached="post.bump_limit_reached"
        :isSticky="post.is_sticky"
        :isBlocked="post.is_blocked"
      />
    </section>
  </div>

  <hr v-if="isContentExist">

  <div v-if="isContentExist" class="board-paginator">
    <PaginationComponent
      :total="count"
      :current="current"
      :per-page="perPage"
      @change="handlePageChange"
    />
  </div>

  <hr v-if="isContentExist">
</div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { bus } from '../bus';
import ThreadComponent from '../components/Thread.vue';
import Form from '../components/Form.vue';
import PaginationComponent from '../components/Pagination.vue';
import { getBoardData } from '../api/boards';

export default {
  name: 'Board',
  components: {
    ThreadComponent, Form, PaginationComponent
  },
  setup() {
    const router = useRouter();
    const route = useRoute();

    const tag = ref(null);
    const name = ref('');
    const threads = ref([]);
    const isFormVisible = ref(false);
    const count = ref(0);
    const current = ref(1);
    const perPage = ref(20);

    let formSuccessUnsub = null;

    const isContentExist = computed(() => threads.value.length > 0);
    const isMetaBoard = computed(() => tag.value && tag.value.search(/\+/) !== -1);
    const title = computed(() => 'U III E : /' + tag.value);

    const selectThread = (id) => {
      router.push('/thread/' + id);
    };

    const init = (page, no_cache = false) => {
      threads.value = [];
      let offset = (current.value - 1) * perPage.value;

      bus.$emit('app.loader', [true]);

      const params = {
        offset: offset,
        limit: perPage.value,
        no_board_list: true
      };

      if (no_cache) {
        params.no_cache = true;
      }

      getBoardData(tag.value, params).then((response) => {
        count.value = response.data.payload.count;
        threads.value = response.data.payload.posts;
        tag.value = route.params.tag;
        name.value = response.data.payload.name;
        document.title = title.value;
        bus.$emit('app.loader', [false]);
      }).catch((error) => {
        showToast('Произошла ошибка при запросе данных с сервера');
        console.log(error);
        bus.$emit('app.loader', [false]);
      });
    };

    const handlePageChange = (newPage) => {
      current.value = newPage;
      init(newPage);
    };

    const showToast = (message) => {
      alert(message);
    };

    onMounted(() => {
      tag.value = route.params.tag;
      init();

      formSuccessUnsub = bus.$on('form:success', function () {
        isFormVisible.value = false;
        init(0, true);
      });

      bus.$on('board:updated', () => init());
    });

    watch(() => route.path, (to, from) => {
      if (to !== from) {
        tag.value = route.params.tag;
        current.value = 1;
        init();
      }
    });

    return {
      tag,
      name,
      threads,
      isFormVisible,
      count,
      current,
      perPage,
      isContentExist,
      isMetaBoard,
      title,
      selectThread,
      handlePageChange
    };
  },
  beforeUnmount() {
    // Clean up would go here if needed
  }
}
</script>

<style scoped>
.btn-text {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 14px;
    color: var(--color-link);
    text-decoration: underline;
}

.btn-text:hover {
    color: var(--color-link-hover);
}

h1 {
    font-size: 20px;
    text-align: center;
    margin-top: 10px;
}

hr {
    margin: 1rem;
}

.pagination-list li {
    background-color: var(--color-pagination-bg);
}


.media-box {
    margin: 5px;
    display: flex;
    flex-wrap: wrap;
}

.card {
    margin: 5px;
    padding: 10px;
    background-color: var(--color-card-bg);
}

.board-threads {
    overflow-wrap: anywhere;
}

.board-paginator {
    margin-bottom: unset;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--color-modal-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
</style>
