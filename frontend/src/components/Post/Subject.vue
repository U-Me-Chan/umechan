<template>
<span class="tag" :style="{ backgroundColor: 'var(--color-tag-info-bg)', color: 'var(--color-tag-info-text)' }">{{ subject }}</span>
</template>

<script>
export default {
  name: 'Subject',
  props: {
    subject: String
  }
}
</script>


<style scoped>
.post-subject {
    white-space: normal;
}
</style>
