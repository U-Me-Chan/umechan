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
    :current="current"
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
        />
    </section>
  </div>

  <hr v-if="isContentExist">

  <b-pagination
    class="board-paginator"
    v-if="isContentExist"
    :total="count"
    :current="current"
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
import { bus } from '../bus'
import Thread from './Board/Thread.vue'
import Form from './Form.vue'

const axios = require('axios')
const config = require('../../config')

export default {
  name: 'Board',
  components: {
    Thread, Form
  },
  data: function () {
    return {
      tag: null,
      name: '',
      threads: [],
      isFormVisible: false,
      count: 0,
      current: 1,
      perPage: 20,
      tags: [],
      selectedTags: []
    }
  },
  mounted: function () {
    var self = this;

    bus.$on('form:success', function () {
      self.isFormVisible = false;
      self.init();
    })
  },
  created: function () {
    this.tag = this.$route.params.tag;
    this.init();
  },
  computed: {
    title: function () {
      return 'U III E : /' + this.tag;
    },
    isContentExist: function () {
      if (this.threads.length === 0) {
	return false;
      }

      return true;
    },
    isMetaBoard: function () {
      if (this.tag.search(/\+/) !== -1) {
	return true;
      }

      return false;
    }
  },
  methods: {
    selectThread: function (id) {
      this.$router.push('/thread/' + id);
    },
    init: function () {
      this.threads = [];
      var self = this;
      var offset = (this.current - 1) * this.perPage;
      bus.$emit('app.loader', [true]);

      axios.get(config.chan_url + '/v2/board/' + this.tag, {
        params: {
          offset: offset,
          limit: this.perPage
        }
      }).then((response) => {
        self.count = response.data.payload.count;
        self.threads = response.data.payload.posts;
        self.tag = self.$route.params.tag;
	self.name = response.data.payload.name;
        document.title = self.title;
        bus.$emit('app.loader', [false]);
      }).catch((error) => {
        self.$buefy.toast.open('Произошла ошибка при запросе данных с сервера');
        console.log(error);
        bus.$emit('app.loader', [false]);
      });
    }
  },
  watch: {
    '$route': function (to, from) {
      if (to.path !== from.path) {
        this.tag = this.$route.params.tag;
        this.current = 1;
        this.init();
      }
    }
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
    background-color: #fff;
}


.media-box {
    margin: 5px;
    display: flex;
    flex-wrap: wrap;
}

.card {
    margin: 5px;
    padding: 10px;
}

.board-paginator {
  margin-bottom: unset;
}
</style>
