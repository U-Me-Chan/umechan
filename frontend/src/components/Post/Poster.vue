<template>
<span class="poster">
  <b-tag v-if="isVerify" size="is-small" type="is-success">
    <b-tooltip :label="'Личность этого автора удостоверена'" class="poster-tooltip">{{ poster }}</b-tooltip>
  </b-tag>
  <b-tag v-if="!isVerify" size="is-small" :style="{ backgroundColor: 'var(--color-poster-tag-bg)', color: 'var(--color-poster-tag-text)' }">{{ poster }}</b-tag>
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
