<template>
<div class="post-message" v-html="formattedMessage">
</div>
</template>

<script>
import { parse } from '../../utils/post_parser'
import Post from '../Post.vue'

const axios = require('axios');
const config = require('../../../config');

export default {
  name: 'Message',
  props: {
    message: {
      type: String,
      default: ''
    }
  },
  computed: {
    formattedMessage: function () {
      return parse(this.message)
    }
  },
  created: function () {
    const self = this;
    window.onmouseover = function (event) {
      if (event.target.className !== 'reply-link') {
        return;
      }
      
      axios.get(config.chan_url + '/v2/post/' + event.target.attributes[1].value + '/?no_board_list=true').then((response) => {
        const post_data = response.data.payload.thread_data;
        
        self.$buefy.modal.open({
          parent: self,
          component: Post,
          props: {
            id: post_data.id,
            poster: post_data.poster,
            subject: post_data.subject,
            youtubes: post_data.media.youtubes,
            images: post_data.media.images,
            videos: post_data.media.videos,
            isVerfiy: post_data.is_verify,
            parentId: post_data.parent_id,
            datetime: post_data.datetime,
            message: post_data.truncated_message,
            repliesCount: post_data.replies_count,
            isSticky: post_data.is_sticky
            
          },
          trapFocus: true,
          overlay: false,
          customClass: 'no-overlay',
        });

      })
    }
  }
}
</script>

<style>
.post-message {
    margin: 10px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    font-size: 14px;
}

pre {
    background-color: #f5f2f0;
    max-width: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.blockquote {
    margin-left: 0px;
    padding-left: 5px;
    color: #083;
}

.hashtag {
    padding-left: 5px;
    color: red;
}

ol {
    padding-left: 30px;
}

table, tr, th, td {
    border: 1px solid #ddd;
}

ul {
    list-style: inside;
}

.no-overlay .modal-background {
    background-color: transparent !important;
}
</style>
  
