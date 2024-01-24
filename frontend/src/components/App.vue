<template>
<div id="app">
  <div class="board-list">
    <a :href="'/board/' + board.tag" class="board-link" v-for="board in boards" @click="selectBoard(board.tag, $event)" :key="board.id" v-bind:class="{ active: tag === board.tag }">{{ board.name }} (+{{ board.new_posts_count }}) </a>
  </div>

  <div class="columns">
    <div class="main-content column">
      <b-loading :can-cancel="true" v-model="isLoading" :isFullPage="true"></b-loading>
      <router-view/>
    </div>

    <Radio class="column radio-content"/>

    <div class="auth-panel">
      <AuthPanel/>
    </div>
  </div>

  <footer class="footer">
    <div class="content has-text-centered">
      <p>
	<strong>U III E</strong> 2011-2077
      </p>
    </div>
  </footer>
</div>
</template>

<script>
import axios from 'axios'
import { bus} from '../bus'
import Radio from './Radio.vue'
import AuthPanel from './AuthPanel.vue'

const config = require('../../config')

export default {
  name: 'App',
  components: {
    Radio, AuthPanel
  },
  data: function () {
    return {
      boards: [],
      tag: '',
      posts: [],
      isLoading: false,
      title: 'U III E'
    }
  },
  created: function () {
    this.isLoading = true;
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
  computed: {
    allTags: function () {
      return this.boards.map(function (board) {
	return board.tag;
      }).join('+');
    }
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
        bus.tags = self.boards.map(function (board) {
          return board.tag;
        });
        self.isLoading = false;
      }).catch((error) => {
        self.$buefy.toast.open('Произошла ошибка при запросе данных с сервера')
        console.log(error);
        self.isLoading = false;
      })
    },
    saveAdminKey: function () {
      
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
    color: white;
    background-color: #7957d5;
    border: 1px solid;
}

.board-list {
    display:flex;
    justify-content: space-around;
    background-color: #fff;
    flex-wrap: wrap;
}

.board-link {
    margin: 0px;
}

.main-content {
    margin: 10px 500px 10px 150px;
    box-sizing: border-box;
}

.radio-content {
    position: fixed;
    background-color: white;
    border: 2px dotted grey;
    bottom: 0;
    right: 0;
}

@media (max-width: 1300px) {
    .main-content {
	margin: 10px 5px 10px 5px;
    }
    .radio-content {
	display: none;
	visibility: hidden;
    }

    .auth-panel {
	display: none;
	visibility: hidden;
    }
}
</style>
