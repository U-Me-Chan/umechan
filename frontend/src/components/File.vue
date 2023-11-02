<template>
<div class="file card">
  <a :href="original" target="_blank"><img :src="thumbnail"></a>
  <a href="#" v-for="id in postIds" :key="id" @click="selectThread(id)">Post #{{id}}</a>
  <span v-if="postIds.length === 0">Нет постов</span>
</div>
</template>

<script>
const axios = require('axios')
const config = require('../../config')

export default {
  name: 'File',
  props: {
    name: String,
    original: String,
    thumbnail: String
  },
  data: function () {
    return {
      postIds: []
    }
  },
  methods: {
    getPostIds: function () {
      var self = this

      axios.get(config.filestore_url + '/files/' + this.name).then((response) => {
	self.postIds = response.data.post_ids
      }).catch((error) => {
	console.log(error)
      })
    },
    selectThread: function (id) {
      this.$router.push('/thread/' + id)
    }
  },
  created: function () {
    this.getPostIds();
  }
}
</script>

<style scoped>
.card {
    margin: 10px;
    padding: 5px;
}

img {
    width: 200px;
}

.file {
    display: flex;
    flex-direction: column;
}
</style>
