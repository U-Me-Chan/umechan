<template>
<div class="board">
  <h1>{{ tag }}</h1>

  <div v-if="!isMetaBoard">
    <center><b-button type="is-text" @click="isFormVisible = !isFormVisible">Создать тред</b-button></center>
    <b-modal v-model="isFormVisible">
      <Form v-if="isFormVisible" :tag="tag"/>
    </b-modal>
  </div>
  <hr v-if="isContentExist">

  <b-pagination
    class="board-paginator"
    v-if="isContentExist"
    :total="count"
    :current-page="current"
    :per-page="perPage"
    v-model="current"
    v-on:change="init"
    rangeBefore="2"
    rangeAfter="2"
    order="is-centered"
    size="is-small">
  </b-pagination>

  <div class="board-threads">
    <section class="threads" v-for="post in threads" :key="post.id">
      <hr v-if="isContentExist">
      <Thread
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

  <b-pagination
    class="board-paginator"
    v-if="isContentExist"
    :total="count"
    :current-page="current"
    :per-page="perPage"
    v-model="current"
    v-on:change="init"
    rangeBefore="2"
    rangeAfter="2"
    order="is-centered"
    size="is-small">
  </b-pagination>

  <hr v-if="isContentExist">
</div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';

import { bus } from '../bus'
import Thread from '../components/Thread.vue'
import Form from '../components/Form.vue'
import { getBoardData } from '../api/boards'
import { showToast } from '../utils/toast'

export default {
  name: 'Board',
  components: {
    Thread, Form
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
    const tags = ref([]);
    const selectedTags = ref([]);

    const isContentExist = computed(() => threads.value.length > 0);
    const isMetaBoard = computed(() => tag.value && tag.value.search(/\+/) !== -1);

    const init = function (page, no_cache = false) {
      threads.value = [];
      var offset = (current.value - 1) * perPage.value;
      bus.$emit('app.loader', [true]);

      const params = {
        offset: offset,
        limit: perPage.value,
        no_board_list: true
      }

      if (no_cache) {
        params.no_cache = true;
      }

      getBoardData(tag.value, params).then((response) => {
        count.value = response.data.payload.count;
        threads.value = response.data.payload.posts;
        tag.value = route.params.tag;
        name.value = response.data.payload.name;
        document.title = 'U III E : /' + tag.value;
        bus.$emit('app.loader', [false]);
      }).catch((error) => {
        showToast({ message: 'Произошла ошибка при запросе данных с сервера', type: 'is-danger' });
        console.log(error);
        bus.$emit('app.loader', [false]);
      });
    };

    const selectThread = function (id) {
      router.push('/thread/' + id);
    };

    let formSuccessHandler;

    onMounted(() => {
      tag.value = route.params.tag;
      init();

      formSuccessHandler = () => {
        isFormVisible.value = false;
        init(0, true);
      };

      bus.$on('form:success', formSuccessHandler);
      bus.$on('board:updated', () => init());
    });

    watch(() => route.params.tag, () => {
      tag.value = route.params.tag;
      current.value = 1;
      init();
    });

    return {
      tag,
      name,
      threads,
      isFormVisible,
      count,
      current,
      perPage,
      tags,
      selectedTags,
      isContentExist,
      isMetaBoard,
      selectThread,
      init
    };
  }
}
</script>

<style scoped>
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
</style>
