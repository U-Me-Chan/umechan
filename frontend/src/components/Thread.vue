<template>
<div class="thread" ref="thread-top" id="thread-top">
  <h3>{{opPost.boardName}}: {{opPost.subject ? opPost.subject : '...'}}</h3>

  <Post :id="opPost.id"
        :poster="opPost.poster"
        :subject="opPost.subject"
        :message="opPost.message"
        :parentId="opPost.parent_id"
	:repliesCount="opPost.repliesCount"
	:youtubes="opPost.youtubes"
	:images="opPost.images"
	:isVerify="opPost.isVerify"
        :isThread="true"
	:datetime="opPost.datetime"/>

  <Post class="post-reply"
        v-for="post in replies" :key="post.id"
        :id="post.id"
        :poster="post.poster"
        :subject="post.subject"
        :message="post.truncated_message"
        :images="post.media.images"
        :youtubes="post.media.youtubes"
	:isVerify="post.is_verify"
        :parentId="post.parent_id"
	:datetime="post.datetime"/>
</div>
</template>

<script>
import Post from './Post.vue'
import { bus } from '../bus'

const config = require('../../config')
const axios  = require('axios');

export default {
  name: 'Thread',
  components: {
    Post
  },
  methods: {
    init: function () {
      var self = this;
      
      bus.$emit('app.loader', [true]);
      
      axios.get(config.chan_url + '/v2/post/' + this.id).then((response) => {
        if (response.data.payload.thread_data.parent_id !== null) {
          self.id = response.data.payload.thread_data.parent_id;
          self.init();
        }
        
        self.opPost = {
          id: response.data.payload.thread_data.id,
          poster: response.data.payload.thread_data.poster,
          subject: response.data.payload.thread_data.subject,
          message: response.data.payload.thread_data.truncated_message,
          repliesCount: response.data.payload.thread_data.replies_count,
          images: response.data.payload.thread_data.media.images,
          youtubes: response.data.payload.thread_data.media.youtubes,
          isVerify: response.data.payload.thread_data.is_verify,
          datetime: response.data.payload.thread_data.datetime,
          boardName: response.data.payload.thread_data.board.name
        };
        
        self.replies = response.data.payload.thread_data.replies;
        
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
      
      //this.$nextTick(() => el.scrollIntoView())
      
      if (type == 'post') {
        el.classList.add('post-active');
      }
    }
  },
  created: function () {
    this.id = this.$route.params.id;
    this.init();
  },
  mounted: function () {
    setTimeout(() => {
      var section = this.$router.currentRoute.hash.replace('#', '');
      
      if (section) {
        this.scrollTo(section, 'post');
      } else {
        this.scrollTo('thread-top');
      }
    }, 1500);
    
    var self = this;
    
    // FIXME: use vues, Luke
    bus.$on('form:success', () => self.init());
  },
  watch:  {
    '$route': function (to, from) {
      if (to !== from) {
        this.id = this.$route.params.id;
        this.init();
        
        var section = this.$router.currentRoute.hash.replace('#', '');
        
        if (section) {
          setTimeout(() => {
            this.scrollTo(section, 'post');
          }, 1500);
        }
      }
    }
  },
  data: function () {
    return {
      id: false,
      opPost: false,
      replies: [],
      countReplies: 0,
      isFormVisible: false
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
}

.post-reply {
    background-color: #fff;    
}

.card {
    margin: 5px;
    padding: 10px;
}
</style>
