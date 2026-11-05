<template>
<div class="post card" :id="id" :ref="id">
  <MetaComponent
    :id="id"
    :poster="poster"
    :subject="subject"
    :datetime="datetime"
    :isVerify="isVerify"
    :parentId="parentId"
    :isSticky="isSticky"
    :board="board"
    :repliesCount="repliesCount"
    :isBumpLimit="isBumpLimit"
    :isBlocked="isBlocked"
  />
  <div class="post-body">
    <MediaComponent :images="images" :youtubes="youtubes" :videos="videos"/>
    <MessageComponent :message="message"/>
  </div>
</div>
</template>

<script>
import MetaComponent from './Post/Meta.vue';
import MessageComponent from './Post/Message.vue';
import MediaComponent from './Post/Media.vue';

export default {
  name: 'Post',
  components: {
    MetaComponent, MessageComponent, MediaComponent
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
    isBlocked: {
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
    background-color: var(--color-card-bg);
}

.post-body {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
}
</style>
