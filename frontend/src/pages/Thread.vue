<template>
<div class="thread" ref="thread-top" id="thread-top">
  <h3>{{post.board.name}}(/{{post.board.tag}}/): {{post.subject ?? '...'}}</h3>
  
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
import { bus } from '../bus'
import Thread from '../components/Thread.vue'

const config = require('../../config')
const axios  = require('axios');

export default {
  name: 'ThreadPage',
  components: {
    Thread
  },
  methods: {
    init: function () {
      var self = this;

      bus.$emit('app.loader', [true]);

      axios.get(config.chan_url + '/v2/post/' + this.id + '/?no_board_list=true').then((response) => {
        if (response.data.payload.thread_data.parent_id !== null) {
          self.id = response.data.payload.thread_data.parent_id;
          self.init();
        }

        self.post = response.data.payload.thread_data;

        bus.$emit('boards.update', [response.data.payload.thread_data.board.tag]);
        bus.$emit('app.loader', [false]);

        document.title = 'U III E : /' + response.data.payload.thread_data.board.tag + '/' + response.data.payload.thread_data.subject;
      }).catch((error) => {
        console.log(error);
        self.$buefy.toast.open(`Произошла ошибка при запросе данных треда: ${error}`);
        bus.$emit('app.loader', [false]);
      });
    },
    scrollTo: function (section, type) {
      var el = window.document.getElementById(section);

      this.$nextTick(() => el.scrollIntoView())

      if (type == 'post') {
        el.classList.add('post-active');
      }
    }
  },
  created: function () {
    this.id = this.$route.params.id;
    this.init();

    bus.$on('thread:updated', () => this.init())
  },
  updated: function () {
    var section = this.$router.currentRoute.hash.replace('#', '');

    if (section) {
      this.scrollTo(section, 'post');
    }

    var self = this;

    bus.$on('form:success', () => self.init());
  },
  watch:  {
    '$route': function (to, from) {
      if (to !== from) {
        this.id = this.$route.params.id;
        this.init();

        var section = this.$router.currentRoute.hash.replace('#', '');

        if (section) {
          this.scrollTo(section, 'post');
        }
      }
    }
  },
  data: function () {
    return {
      id: false,
      post: false
    }
  }
}
</script>

<style scoped>
.toggle-form {
    cursor: pointer;
    font-weight: bold;
    padding: 5px;
    background-color: #333;
    text-align: center;
    width: 95%;
    border: 1px #444 solid;
}

.toggle-form:hover {
    color: #aaa;
}

h3 {
    font-size: 20px;
    text-align: center;
    margin-top: 10px;
}

.thread {
    background-color: #eee;
    margin-top: 0;
    overflow-wrap: anywhere;
    max-width: 1280px;
}

.post-reply {
    background-color: #fff;
}

.card {
    margin: 5px;
    padding: 10px;
}

.post-active {
    border: 2px dotted blue;
}
</style>
