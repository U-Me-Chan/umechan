<template>
<b-tag size="is-small" type="is-info is-light">{{ subject }}</b-tag>
</template>

<script>
export default {
  name: 'Subject',
  props: {
    subject: String
  }
}
</script>
