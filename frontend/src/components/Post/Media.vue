<template>
<div class="post-media">
  <p class="image-box" v-for="image in images" :key="image.link">
    <Picture :originalUrl="image.link" :previewUrl="image.preview"/>
  </p>

  <p class="youtube-box" v-for="youtube in youtubes" :key="youtube.link">
    <Youtube :originalUrl="youtube.link" :previewUrl="youtube.preview"/>
  </p>

  <p class="video-box" v-for="video in videos" :key="video.link">
    <Video :originalUrl="video.link" :previewUrl="video.preview"/>
  </p>
</div>
</template>

<script>
import Picture from './Media/Picture.vue'
import Youtube from './Media/Youtube.vue'
import Video from './Media/Video.vue'

export default {
  name: 'Media',
  components: {
    Picture, Youtube, Video
  },
  props: {
    images: Array,
    youtubes: Array,
    videos: Array
  }
}
</script>

<style>
.post-media {
    display: flex;
    flex-wrap: wrap;
}
</style>
