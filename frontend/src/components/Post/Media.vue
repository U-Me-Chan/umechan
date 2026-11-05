<template>
<div class="post-media">
  <p class="image-box" v-for="image in images" :key="image.link">
    <PictureComponent :originalUrl="image.link" :previewUrl="image.preview"/>
  </p>

  <p class="youtube-box" v-for="youtube in youtubes" :key="youtube.link">
    <YoutubeComponent :originalUrl="youtube.link" :previewUrl="youtube.preview"/>
  </p>

  <p class="video-box" v-for="video in videos" :key="video.link">
    <VideoComponent :originalUrl="video.link" :previewUrl="video.preview"/>
  </p>
</div>
</template>

<script>
import PictureComponent from './Media/Picture.vue';
import YoutubeComponent from './Media/Youtube.vue';
import VideoComponent from './Media/Video.vue';

export default {
  name: 'Media',
  components: {
    PictureComponent, YoutubeComponent, VideoComponent
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
