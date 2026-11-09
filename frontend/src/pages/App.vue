<template>
<div id="app">
  <header class="board-list">
    <a
      :href="`/board/${board.tag}`"
      class="board-link"
      v-for="board in boards"
      @click="selectBoard(board.tag, $event)"
      :key="board.id"
      v-bind:class="{ active: tag === board.tag }"
      >
      {{ board.name }} {{ board.new_posts_count ? `(+${board.new_posts_count})` : '' }}
    </a>

    <div class="theme-toggle-wrap">
      <b-tooltip :label="isDark ? 'Светлая тема' : 'Тёмная тема'" position="is-bottom">
        <b-switch
          v-model="isDark"
          type="is-info"
          size="is-small"
          rounded
          class="theme-switch">
          <span class="theme-switch-label">
            <template v-if="!isDark">🌙</template>
            <template v-else>☀️</template>
          </span>
        </b-switch>
      </b-tooltip>
    </div>
  </header>

  <main class="columns-wrap">
    <div class="main-content">
      <b-loading :can-cancel="true" v-model="isLoading" :isFullPage="true"></b-loading>
      <router-view/>
    </div>

    <div class="side-content-fixed">
      <div class="radios-panel-wrap">
        <Radio class="radio-content"/>
      </div>
    </div>
  </main>

  <footer class="footer-wrap">
    <div class="footer-content has-text-centered">
      <p>
        <strong>U III E</strong> 2011-2077
      </p>
    </div>
  </footer>
</div>
</template>

<script>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { bus } from '../bus'
import Radio from '../components/Radio/Radio.vue'
import { getBoardList } from '../api/boards'
import { showToast } from '../utils/toast'
import themeStore from '../stores/theme'

export default {
  name: 'App',
  components: {
    Radio
  },
  setup() {
    const router = useRouter();
    const boards = ref([]);
    const tag = ref('');
    const isLoading = ref(false);
    const title = ref('U III E');
    const isDark = ref(themeStore.state.current === 'dark');

    const selectBoard = function (selectedTag, event) {
      event.preventDefault();
      tag.value = selectedTag;
      router.push('/board/' + selectedTag);
    };

    const updateData = function () {
      getBoardList().then((response) => {
        boards.value = response.data.payload.boards;
      }).catch((error) => {
        showToast({ message: 'Произошла ошибка при запросе данных с сервера', type: 'is-danger' });
        console.error(error);
      });
    };

    let boardsUpdateHandler;
    let appLoaderHandler;
    let intervalId;

    onMounted(() => {
      updateData();
      document.title = title.value;
      intervalId = setInterval(() => updateData(), 30000);

      boardsUpdateHandler = (params) => {
        tag.value = params[0];
      };
      appLoaderHandler = (args) => {
        isLoading.value = args[0];
      };

      bus.$on('boards.update', boardsUpdateHandler);
      bus.$on('app.loader', appLoaderHandler);
    });

    onUnmounted(() => {
      clearInterval(intervalId);
      bus.$off('boards.update', boardsUpdateHandler);
      bus.$off('app.loader', appLoaderHandler);
    });

    watch(isDark, () => {
      themeStore.toggle();
    });

    return {
      boards,
      tag,
      isLoading,
      title,
      isDark,
      selectBoard,
      updateData
    };
  }
}
</script>

<style>
html {
    background-color: var(--color-bg) !important;
}
#app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

p {
    margin: 10px;
}

.board-list {
    display:flex;
    justify-content: space-around;
    flex-wrap: wrap;
    min-height: 36px;
}

.board-link {
    margin: 0px;
}

.columns-wrap {
    margin-block: 10px;
}

.main-content,
.footer-content {
    max-width: 1280px;
    margin-inline: 150px 350px;
    box-sizing: border-box;
}

.main-content {
    margin-block: 10px;
    box-sizing: border-box;
}

.side-content-fixed {
    position: fixed;
    display: flex;
    flex-direction: column-reverse;
    bottom: 200px;
    right: 0px;
    gap: 30px;
    width: 300px;
}

.footer-wrap {
    padding-block: 45px 90px;
}

@media (max-width: 1300px) {
  .main-content {
      margin-inline: 5px;
  }

  .side-content-fixed {
      display: none;
      visibility: hidden;
  }

  .footer-content {
      margin-inline: unset;
  }
}

.theme-toggle-wrap {
    display: flex;
    align-items: center;
    padding: 5px 10px;
}

.theme-switch-label {
    font-size: 14px;
    margin-left: 4px;
    user-select: none;
}

.theme-switch .switch-track {
    background-color: var(--color-tag-bg);
    border: 1px solid var(--color-border);
}

.theme-switch.is-info .switch-checked .switch-thumb {
    background-color: var(--color-accent);
}

.theme-switch .switch-track {
    box-shadow: 0 0 1px var(--color-shadow);
}

@media (max-width: 1300px) {
    .theme-toggle-wrap {
        justify-content: center;
    }
}
</style>
