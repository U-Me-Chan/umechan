<template>
<div class="file-list">
  <div class="admin-panel">
    <input type="text" :value="admin_key" placeholder="admin_key">
  </div>

  <h1>Files</h1>

  <b-pagination
    :total="count"
    :current="current"
    :per-page="perPage"
    v-model="current"
    v-on:change="getFiles"
    rangeBefore="2"
    rangeAfter="2"
    order="is-centered"
    size="is-small">
  </b-pagination>

  <div class="files">
    <div class="file card" v-for="file in files" :key="file">
      <a :href="file.original" target="_blank"><img :src="file.thumbnail"></a>
    </div>
  </div>
</div>
</template>

<script>
import axios from 'axios'
import { bus } from '../bus'

const config = require('../../config')

export default {
  data: function () {
    return {
      files: [],
      count: 0,
      current: 1,
      perPage: 20
    }
  },
  created: function  () {
    this.getFiles()
  },
  methods: {
    getFiles: function () {
      var self = this
      var offset = (this.current - 1) * this.perPage
      bus.$emit('app.loader', [true])

      axios.get(config.filestore_url + '/files', {
        params: {
          offset: offset,
          limit: this.perPage
        }
      }).then((response) => {
        self.files = response.data.files
        self.count = response.data.count
        bus.$emit('app.loader', [false])
      }).catch((error) => {
        console.log(error)
        bus.$emit('app.loader', [false])
      })
    }
  }
}
</script>

<style scoped>
h1 {
    font-size: 40px;
    text-align: center;
    margin: 20px;
}

.files {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
}

img {
    width: 200px;
}

.file {
    display: flex;
    flex-direction: column;
}

.admin-panel {
    position: fixed;
    right:0;
    bottom: 250px;
    background-color: white;
    border: 1px dotted grey;
}

.card {
    margin: 10px;
    padding: 5px;
}
</style>
