<template>
<div id="app">
  <header class="board-list">
    <a
      :href="`/board/${board.tag}`"
      class="board-link"
      v-for="board in boards"
      @click="selectBoard(board.tag, $event)"
      :key="board.id"
      :class="{ active: tag === board.tag }"
    >
      {{ board.name }} {{ board.new_posts_count ? `(+${board.new_posts_count})` : '' }}
    </a>

    <div class="theme-toggle-wrap">
      <span :title="isDark ? 'Светлая тема' : 'Тёмная тема'" class="tooltip">
        <label class="theme-switch-label" @click="toggleTheme">
          <span class="theme-switch">
            <input type="checkbox" v-model="isDark" />
            <span class="slider"></span>
          </span>
          <span class="theme-switch-label-text">
            <template v-if="!isDark">🌙</template>
            <template v-else>☀️</template>
          </span>
        </label>
      </span>
    </div>
  </header>

  <main class="columns-wrap">
    <div class="main-content">
      <div v-if="isLoading" class="loading-overlay">
        <div class="loading-spinner"></div>
      </div>
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
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { bus } from '../bus';
import Radio from '../components/Radio/Radio.vue';
import { getBoardList } from '../api/boards';
import themeStore from '../stores/theme';

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

    let boardsUpdateUnsub = null;
    let appLoaderUnsub = null;

    const toggleTheme = () => {
      themeStore.toggle();
      isDark.value = themeStore.state.current === 'dark';
    };

    const selectBoard = (tagValue, event) => {
      event.preventDefault();
      tag.value = tagValue;
      router.push('/board/' + tagValue);
    };

    const updateData = () => {
      getBoardList().then((response) => {
        boards.value = response.data.payload.boards;
      }).catch((error) => {
        showToast('Произошла ошибка при запросе данных с сервера');
        console.error(error);
      });
    };

    const showToast = (message) => {
      alert(message); // Simple toast replacement since Buefy is removed
    };

    let interval = null;

    const cleanup = () => {
      if (interval) clearInterval(interval);
      if (boardsUpdateUnsub) boardsUpdateUnsub();
      if (appLoaderUnsub) appLoaderUnsub();
    };

    onMounted(() => {
      updateData();
      document.title = title.value;

      interval = setInterval(() => updateData(), 30000);

      boardsUpdateUnsub = bus.$on('boards.update', function (params) {
        tag.value = params[0];
      });

      appLoaderUnsub = bus.$on('app.loader', function (args) {
        isLoading.value = args[0];
      });
    });

    onUnmounted(cleanup);

    return {
      boards,
      tag,
      isLoading,
      title,
      isDark,
      selectBoard,
      toggleTheme
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
    position: relative;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--color-loading-overlay);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid var(--color-border);
    border-top-color: var(--color-accent);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
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
    cursor: pointer;
}

.theme-switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}

.theme-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--color-tag-bg);
    border: 1px solid var(--color-border);
    transition: 0.3s;
    border-radius: 20px;
    box-shadow: 0 0 1px var(--color-shadow);
}

.slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 2px;
    bottom: 2px;
    background-color: var(--color-accent);
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--color-accent);
}

input:checked + .slider:before {
    transform: translateX(18px);
}

.theme-switch-label-text {
    font-size: 14px;
}

@media (max-width: 1300px) {
    .theme-toggle-wrap {
        justify-content: center;
    }
}
</style>
