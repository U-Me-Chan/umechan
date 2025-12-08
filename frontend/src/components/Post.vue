<template>
<div class="post card" :id="id" :ref="id">
  <Meta
    :id="id"
    :poster="poster"
    :subject="subject"
    :datetime="datetime"
    :isVerify="isVerify"
    :parentId="parentId"
    :isSticky="isSticky"
    :board="board"
    :repliesCount="repliesCount"
    />
  <div class="post-body">
    <Media :images="images" :youtubes="youtubes" :videos="videos"/>
    <Message :message="message"/>
  </div>
</div>
</template>

<script>
import Message from './Post/Message.vue'
import Meta from './Post/Meta.vue'
import Media from './Post/Media.vue'

export default {
  name: 'Post',
  components: {
    Message, Meta, Media
  },
  props: {
    id: Number,
    poster: String,
    subject: String,
    datetime: String,
    message: String,
    images: Array,
    youtubes: Array,
    videos: Array,
    isVerify: {
      type: Boolean,
      default: false
    },
    isSticky: {
      type: Boolean,
      default: false
    },
    isBumpLimit: {
      type: Boolean,
      default: false
    },
    repliesCount: {
      type: [Number, Boolean],
      default: false
    },
    parentId: {
      type: [Boolean, Number],
      default: false
    },
    board: Object,
  }
}
</script>

<style scoped>
.card {
    margin: 5px;
    padding: 10px;
}

.post-body {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
}
</style>
