<template>
<div class="file-list">
  <h1>Files</h1>

  <b-pagination
    :total="count"
    :current="current"
    :per-page="perPage"
    :page-input="true"
    v-model="current"
    v-on:change="changePage"
    order="is-centered"
    size="is-small"
    >
  </b-pagination>

  <div class="files">
    <File :name="file.name" :original="file.original" :thumbnail="file.thumbnail" v-for="file in files" :key="file.name"/>
  </div>
</div>
</template>

<script>
import axios from 'axios'
import { bus } from '../bus'
import File from '../components/Admin/File.vue'

const config = require('../../config')

export default {
  components: {
    File
  },
  data: function () {
    return {
      files: [],
      count: 0,
      current: 1,
      perPage: 6
    }
  },
  created: function () {
    this.init()

    var self = this

    bus.$on('files.file.deleted', function () {
      self.getFiles(self.current)
    })
  },
  methods: {
    changePage: function (page) {
      this.$router.push('/admin/files/' + page)
    },
    init: function () {
      this.getFiles(this.current)
    },
    getFiles: function (page) {
      var self = this
      bus.$emit('app.loader', [true])

      var offset = page - 1
      offset = offset * this.perPage

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
  },
  watch: {
    '$route': function(to, from) {
      if (to.path !== from.path) {
        this.init()
      }
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
</style>
