<template>
<div class="post card" :id="id" :ref="id">
  <Meta :id="id" :poster="poster" :subject="subject" :datetime="datetime" :isVerify="isVerify" :parentId="parentId"/>
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
    isVerify: Boolean,
    subject: String,
    parentId: {
      type: [Boolean, Number],
      default: false
    },
    datetime: String,
    message: String,
    images: Array,
    youtubes: Array,
    videos: Array
  }
}
</script>

<style scoped>
</style>
