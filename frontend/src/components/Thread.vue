<template>
<div class="thread" :id="id" :ref="id">
  <Post
    :id="id"
    :poster="poster"
    :subject="subject"
    :datetime="datetime"
    :isVerify="isVerify"
    :parentId="parentId"
    :message="message"
    :images="images"
    :youtubes="youtubes"
    :videos="videos"
    :repliesCount="repliesCount"
    :isSticky="isSticky"
    :isBlocked="isBlocked"
    :isBumpLimit="isBumpLimitReached"
    :board="board"
    />
  
  <div class="replies" v-if="replies.lenght !== 0">
    <Post
      v-for="post in replies" :key="post.id"
      :id="post.id"
      :poster="post.poster"
      :subject="post.subject"
      :datetime="post.datetime"
      :isVerify="post.is_verify"
      :parentId="id"
      :message="post.truncated_message"
      :images="post.media.images"
      :youtubes="post.media.youtubes"
      :videos="post.media.videos"
      :isBlocked="isBlocked"
      />
  </div>
</div>
</template>

<script>
import Post from  './Post.vue'

export default {
  name: 'Thread',
  components: {
    Post
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
    },
    isBlocked: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style>
.post-body {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
}

.thread {
    padding-top: 5px;
    padding-bottom: 5px;
}

.replies {
    border-left: 3px solid grey;
    margin-left: 5px;
}
</style>
