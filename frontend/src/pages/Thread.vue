<template>
<div class="thread" ref="threadTop" id="thread-top">
  <h3>/{{post.board.tag}}/:{{post.subject ?? '...'}}</h3>

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
  />
</div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { bus } from '../bus';
import { getThreadData } from '../api/posts';
import ThreadComponent from '../components/Thread.vue';

export default {
  name: 'ThreadPage',
  components: {
    ThreadComponent
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const threadTop = ref(null);
    const id = ref(false);
    const post = ref(false);

    let formSuccessUnsub = null;
    let threadUpdatedUnsub = null;

    const init = () => {
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
        showToast(`Произошла ошибка при запросе данных треда: ${error}`);
        bus.$emit('app.loader', [false]);
      });
    };

    const scrollTo = (section) => {
      if (section === "") return;

      const el = window.document.getElementById(section);
      if (el == null) return;

      setTimeout(() => el.scrollIntoView(), 0);
      el.classList.add('post-active');
    };

    const showToast = (message) => {
      alert(message);
    };

    onMounted(() => {
      id.value = route.params.id;
      init();

      formSuccessUnsub = bus.$on('form:success', () => init());
      threadUpdatedUnsub = bus.$on('thread:updated', () => init());
    });

    onBeforeUnmount(() => {
      if (formSuccessUnsub) formSuccessUnsub();
      if (threadUpdatedUnsub) threadUpdatedUnsub();
    });

    return {
      threadTop,
      id,
      post,
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
