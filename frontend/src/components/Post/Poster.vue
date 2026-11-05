<template>
<span class="poster">
  <span v-if="isVerify" class="tag is-success" title="Личность этого автора удостоверена">
    {{ poster }}
  </span>
  <span v-if="!isVerify" class="tag" :style="{ backgroundColor: 'var(--color-poster-tag-bg)', color: 'var(--color-poster-tag-text)' }">
    {{ poster }}
  </span>
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
