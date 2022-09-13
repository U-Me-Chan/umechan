<template>
<a target="_blank" :href="originalUrl"><img :src="previewUrl"></a>
</template>

<script>
export default {
  name: 'Youtube',
  props: {
    originalUrl: String,
    previewUrl: String
  }
}
</script>

<style scoped>
img {
    border: 3px solid red;
    max-width: 240px;
    max-height: 200px;
    widht: auto;
}
</style>
