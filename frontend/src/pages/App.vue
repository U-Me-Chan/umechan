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
import axios from 'axios'
import { bus } from '../bus'
import Radio from '../components/Radio/Radio.vue'

const config = require('../../config')

export default {
  name: 'App',
  components: {
    Radio
  },
  data: function () {
    return {
      boards: [],
      tag: '',
      isLoading: false,
      title: 'U III E'
    }
  },
  created: function () {
    this.updateData();
    document.title = this.title;

    setInterval(() => this.updateData(), 30000);
  },
  mounted: function () {
    var self = this

    bus.$on('boards.update', function (params) {
      self.tag = params[0]
    })

    bus.$on('app.loader', function (args) {
      self.isLoading = args[0]
    })
  },
  methods: {
    selectBoard: function (tag, event) {
      event.preventDefault();
      this.tag = tag;
      this.$router.push('/board/' + tag);
    },
    updateData: function () {
      var self = this;

      axios.get(config.chan_url + '/v2/board').then((response) => {
        self.boards = response.data.payload.boards;
      }).catch((error) => {
        self.$buefy.toast.open('Произошла ошибка при запросе данных с сервера')
        console.log(error);
      })
    }
  }
}
</script>

<style>
#app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

body {
    background-color: #ccc;
    color: #000;
}

a {
    color: #a00;
    padding: 5px;
}

p {
    margin: 10px;
}

.active {
    color: #fafafa;
    background-color: #7957d5;
    border: 1px solid;
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

.auth-panel-wrap,
.radios-panel-wrap,
.board-list {
    background-color: #fafafa;
}

.footer-wrap {
    background: linear-gradient(180deg, #fafafa 0%, #ffffff 100%)
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

.auth-panel-wrap,
.radios-panel-wrap {
    border: 2px dotted grey;
    padding: 10px;
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
</style>
