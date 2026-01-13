<template>
<span class="poster">
  <b-tag v-if="isVerify" size="is-small" type="is-success">
    <b-tooltip label="Личность этого автора удостоверена">{{ poster }}</b-tooltip>
  </b-tag>
  <b-tag v-if="!isVerify" size="is-small" type="is-light">{{ poster }}</b-tag>
</span>
</template>

<script>
export default {
  name: 'Poster',
  props: {
    poster: String,
    isVerify: {
      type: Boolean,
      default: false
    }
  }
}
</script>
