<template>
<div class="file-list">
  <h1>Tracks</h1>

  <b-field label="Артист">
    <b-input v-model="artist" value=""></b-input>
  </b-field>

  <b-field label="Имя">
    <b-input v-model="title" value=""></b-input>
  </b-field>

  <!-- <b-field label="Сортировка"> -->
  <!--   <b-select placeholder="Выберите поле" v-model="sortField"> -->
  <!--     <option value="estimate">Оценка</option> -->
  <!--     <option value="artist">Исполнитель</option> -->
  <!--     <option value="title">Композиция</option> -->
  <!--   </b-select> -->
  <!-- </b-field> -->

  <b-button @click="getTracks(current)" type="is-primary">Найти</b-button>

  <b-pagination
    :total="count"
    :current="current"
    :per-page="perPage"
    v-model="current"
    v-on:change="getTracks"
    rangeBefore="2"
    rangeAfter="2"
    order="is-centered"
    size="is-small">
  </b-pagination>

  <section class="tracks" v-for="track in tracks" :key="track.id">
    <Track
      :id="track.id"
      :artist="track.artist"
      :title="track.title"
      :path="track.path"
      :estimate="track.estimate"
      :duration="track.duration"
      />
  </section>

  <b-pagination
    :total="count"
    :current="current"
    :per-page="perPage"
    v-model="current"
    v-on:change="getTracks"
    rangeBefore="2"
    rangeAfter="2"
    order="is-centered"
    size="is-small">
  </b-pagination>
</div>
</template>

<script>
import axios from 'axios'
import { bus } from '../bus'
import Track from './Track.vue'

const config = require('../../config')

export default {
  name: 'Tracks',
  components: {
    Track
  },
  data: function () {
    return {
      tracks: [],
      count: 0,
      current: 1,
      perPage: 10,
      artist: '',
      title: '',
    }
  },
  created: function  () {
    this.getTracks(this.current)
  },
  methods: {
    getTracks: function (page) {
      var self = this
      bus.$emit('app.loader', [true])

      var offset = page - 1
      offset = offset * this.perPage

      axios.get(config.base_url + '/radio/tracks', {
        params: {
          offset: offset,
          limit: this.perPage,
           artist_substr: this.artist,
           title_substr: this.title
        }
      }).then((response) => {
        self.tracks = response.data.payload.tracks
        self.count = response.data.payload.count
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
}
</style>
