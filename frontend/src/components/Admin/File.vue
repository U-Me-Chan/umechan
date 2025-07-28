<template>
<div class="file card">
  <a :href="original" target="_blank"><img :src="thumbnail"></a>
  <a href="#" v-for="id in postIds" :key="id" @click="selectThread(id)">Post #{{id}}</a>
  <span v-if="postIds.length === 0">Нет постов</span>
  <span @click="deleteFile()">delete</span>
</div>
</template>

<script>
const axios = require('axios')
const config = require('../../../config')

import { bus } from '../../bus'

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
    },
    deleteFile: function () {
      var key = ''

      if (this.$cookie.get('admin_key') !== null) {
	key = this.$cookie.get('admin_key')
      } else {
	key = prompt('Admin key', [''])
      }

      var self = this

      axios.delete(config.filestore_url + '/files/' + this.name, {
        data: {},
        headers: {
          'Key': key
        }
      }).then((response) => {
        bus.$emit('files.file.deleted', [response])
      }).catch((error) => {
        console.log(error)

        self.$buefy.toast.open('Произошла ошибка при удалении файла')
      })
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
