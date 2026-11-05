<template>
<a :href="originalUrl" target="_blank"><img :src="previewUrl"></a>
</template>

<script>
export default {
  name: 'Picture',
  props: {
    originalUrl: String,
    previewUrl: String
  }
}
</script>

<style scoped>
img {
    border: 3px solid var(--color-media-border);
    max-width: 240px;
    max-height: 200px;
    width: auto;
}
</style>
