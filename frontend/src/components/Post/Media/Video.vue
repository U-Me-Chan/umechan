<template>
<a :href="originalUrl" target="_blank"><img :src="previewUrl"></a>
</template>

<script>
export default {
  name: 'Video',
  props: {
    originalUrl: String,
    previewUrl: String
  }
}
</script>

<style scoped>
img {
    border: 3px solid #ede7fb;
    max-width: 240px;
    max-height: 200px;
    widht: auto;
}
</style>
