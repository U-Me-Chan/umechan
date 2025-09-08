<template>
<div class="post card" :id="id" :ref="id">
  <Meta
    :id="id"
    :poster="poster"
    :subject="subject"
    :datetime="datetime"
    :isVerify="isVerify"
    :parentId="parentId"
    :board="board"
    :isSticky="isSticky"
    :isBumpLimit="isBumpLimitReached"
    :repliesCount="repliesCount"
    />

  <div class="post-body">
    <Media :images="images" :youtubes="youtubes" :videos="videos"/>
    <Message :message="message"/>
  </div>

  <div class="replies" v-if="replies.lenght !== 0">
    <div class="card" :key="post.id" :ref="post.id" v-for="post in replies">
      <Meta :id="post.id" :poster="post.poster" :subject="post.subject" :datetime="post.datetime" :isVerify="post.is_verify" :parentId="post.parent_id"/>

      <div class="post-body">
        <Media :images="post.media.images" :youtubes="post.media.youtubes" :videos="post.media.videos"/>
        <Message :message="post.truncated_message"/>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import Meta from '../Post/Meta.vue'
import Message from '../Post/Message.vue'
import Media from '../Post/Media.vue'

export default {
  name: 'Thread',
  components: {
    Message, Meta, Media
  },
  props: {
    id: Number,
    poster: String,
    isVerify: {
      type: Boolean,
      default: false
    },
    subject: {
      type: String,
      default: '...'
    },
    parentId: {
      type: [Boolean, Number],
      default: false
    },
    datetime: String,
    message: String,
    images: Array,
    youtubes: Array,
    videos: Array,
    replies: Array,
    board: Object,
    repliesCount: Number,
    isBumpLimitReached: {
      type: Boolean,
      default: false
    },
    isSticky: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style>
.post {
    background-color: #eee;
}

.card {
    margin: 5px;
    padding: 10px;
}

.post-body {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
} 
</style>
