<template>
<span class="poster">
  <b-tag v-if="isVerify" size="is-small" type="is-success">
    <b-tooltip label="Личность этого автора удостоверена"><b>{{ poster }}</b></b-tooltip>
  </b-tag>
  <b-tag v-if="!isVerify" size="is-small" type="is-light"><b>{{ poster }}</b></b-tag>
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
