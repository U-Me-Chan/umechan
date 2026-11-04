<template>
<b-tag size="is-small" :style="{ backgroundColor: 'var(--color-tag-info-bg)', color: 'var(--color-tag-info-text)' }">{{ subject }}</b-tag>
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
