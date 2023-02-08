<template>
<b-tag size="is-small" type="post-subject is-info is-light">{{ subject }}</b-tag>
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
