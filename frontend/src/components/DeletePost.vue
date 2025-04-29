<template>
<div class="delete-post">
  <Post v-if="post" :id="post.id"
	:poster="post.poster"
	:subject="post.subject"
	:message="post.truncated_message"
	:parentId="post.parent_id"
	:repliesCount="post.replies_count"
	:youtubes="post.media.youtubes"
	:images="post.media.images"
	:isVerify="post.is_verify"
	:datetime="post.datetite"
	/>

  <b-field label="Причина">
    <b-input max-length="200" type="textarea" placeholder="Укажите причину" v-model="reason"/>
  </b-field>

  <b-button @click="deletePost()">Удалить</b-button>
</div>
</template>

<script>
import axios from 'axios'
import Post from './Post.vue'

const config = require('../../config')

export default {
  name: 'DeletePost',
  components: {
    Post
  },
  data: function () {
    return {
      id: 0,
      post: null,
      reason: 'Not Specified'
    }
  },
  methods: {
    init: function () {
      var self = this

      axios.get(config.chan_url + '/v2/post/' + this.id).then((response) => {
        self.post = response.data.payload.thread_data;
      }).catch((error) => {
        console.log(error)
      })
      
    },
    deletePost: function () {
      var key = ''

      if (this.$cookie.get('admin_key') !== null) {
        key = this.$cookie.get('admin_key')
      } else {
        key = prompt('Admin key', [''])
      }

      var self = this

      axios.delete(config.chan_url + '/_/v2/post/' + this.id, {
        data: {},
        headers: {
          'Key': key,
          'Reason': this.reason,
          'Content-type': 'application/json;charset=utf-8',
          'Accept': 'application/json;charset=utf-8'
        }
      }).then(() => {
        self.$buefy.toast.open('Удалено')
      }).catch((error) => {
        console.log(error)

        self.$buefy.toast.open('Произошла ошибка при удалении поста')
      })
    }
  },
  created: function  () {
    this.id = this.$route.params.id
    this.init()
  }
}
</script>

<style scoped>
h1 {
    font-size: 40px;
    text-align: center;
    margin: 20px;
}

.hero {
    background-color: #fff;
    border-radius: 3px;
    margin: 1px;
}
</style>
