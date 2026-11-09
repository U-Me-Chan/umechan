<template>
  <div class="thread" ref="threadTop" id="thread-top">
    <h3>/{{ post.board.tag }}/:{{ post.subject ?? '...' }}</h3>

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
    />
  </div>
</template>

<script>
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { bus } from '../bus';
import { getThreadData } from '../api/posts';
import Thread from '../components/Thread.vue';
import { showToast } from '../utils/toast';

export default {
  name: 'ThreadPage',
  components: {
    Thread
  },
  setup() {
    const router = useRouter();
    const route = useRoute();

    const post = ref({
      board: { tag: '' },
      id: null,
      parent_id: null,
      replies_count: 0,
      bump_limit_reached: false,
      is_sticky: false
    });
    const id = ref(null);

    const init = function () {
      bus.$emit('app.loader', [true]);

      getThreadData(id.value).then((response) => {
        post.value = response.data.payload.thread_data;

        if (post.value.parent_id !== null) {
          router.push('/thread/' + post.value.parent_id + '/#' + id.value);
          return;
        }

        bus.$emit('boards.update', [post.value.board.tag]);
        bus.$emit('app.loader', [false]);

        document.title = '/'
          + post.value.board.tag
          + '/'
          + post.value.subject
          + ':'
          + post.value.id;
      }).catch((error) => {
        console.error(error);
        showToast({ message: `Произошла ошибка при запросе данных треда: ${error}`, type: 'is-danger' });
        bus.$emit('app.loader', [false]);
      });
    };

    const scrollTo = function (section) {
      if (section === '') {
        return;
      }

      const el = window.document.getElementById(section);

      if (el == null) {
        return;
      }

      nextTick(() => el.scrollIntoView());

      el.classList.add('post-active');
    };

    const threadTop = ref(null);

    onMounted(() => {
      id.value = route.params.id;
      init();

      bus.$on('form:success', () => init());
      bus.$on('thread:updated', () => init());
    });

    watch(() => route.hash, (newHash) => {
      const section = newHash.replace('#', '');
      scrollTo(section);
    });

    watch(() => route.params.id, () => {
      id.value = route.params.id;
      init();

      const section = route.hash.replace('#', '');
      scrollTo(section);
    });

    return {
      post,
      threadTop,
      init,
      scrollTo
    };
  }
}
</script>

<style>
.toggle-form {
    cursor: pointer;
    font-weight: bold;
    padding: 5px;
    background-color: var(--color-toggle-form-bg);
    color: var(--color-toggle-form-text);
    text-align: center;
    width: 95%;
    border: 1px var(--color-toggle-form-border) solid;
}

.toggle-form:hover {
    color: var(--color-link-hover);
}

h3 {
    font-size: 20px;
    text-align: center;
    margin-top: 10px;
}

.thread {
    background-color: var(--color-surface);
    margin-top: 0;
    overflow-wrap: anywhere;
    max-width: 1280px;
}

.post-reply {
    background-color: var(--color-card-bg);
}

.card {
    margin: 5px;
    padding: 10px;
    background-color: var(--color-card-bg);
}

.post-active {
    border: 3px dotted blue;
}
</style>
