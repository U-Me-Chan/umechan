<template>
<div class="track card">
  <b-tag size="is-small" type="is-light is-info" class="id">{{id}}</b-tag>
  <b-tag size="is-small" type="is-light">{{artist}} - {{title}}</b-tag>
</div>
</template>

<script>
export default {
  name: 'Track',
  props: {
    id: Number,
    artist: String,
    title: String,
    path: String,
    estimate: Number,
    duration: Number
  },
  computed: {
    durationString: function () {
      var minutes = Math.floor(this.duration / 60)
      var seconds = this.duration % 60

      return minutes + ':' + seconds
    },
    estimateValue: function () {
      return this.estimate / 100 / 5
    }
  }
}
</script>

<style scoped>
.track {
    padding: 5px;
    margin: 2px;
}

.path, .duration {
    font-size: 0.5em;
}
</style>
