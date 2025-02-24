<template>
<div class="file-list">
  <div class="flex-wrapper">
    <h2>Очередь:</h2>
    <div class="queue-wrapper">
      <section class="queue card" v-for="track in queue" :key="track.path">
        <span>{{track.artist}} - {{track.title}} ({{formatDuration(track.time)}})</span>
      </section>
    </div>

    <h2>Найти и заказать:</h2>
    <div class="tracks-list card">
      <b-table
        :data="tracks"
        :paginated="true"
        :per-page="perPage"
        :current-page.sync="current"
        backend-pagination
        :total="count"
        @page-change="getTracks">

        <b-table-column field="artist" label="Исполнитель" searchable sortable>
          <template slot="searchable">
            <b-input v-model="artist" placeholder="Найти исполнителя"/>
          </template>
          <template v-slot="props">{{props.row.artist}}</template>
        </b-table-column>

        <b-table-column field="title" label="Композиция" searchable sortable>
          <template slot="searchable">
            <b-input v-model="title" placeholder="Найти композицию"/>
          </template>
          <template v-slot="props">{{props.row.title}}</template>
        </b-table-column>

        <b-table-column field="time" label="Длительность" v-slot="props">{{formatDuration(props.row.duration)}}</b-table-column>
        <b-table-column label="Действие" v-slot="props">
          <b-button @click="putTrackToQueue(props.row.id, $event)">Заказать</b-button>
        </b-table-column>
        <span class="button" @click="resetFilters($event)">Очистить фильтры</span>
      </b-table>
    </div>
  </div>
</div>
</template>

<script>
import axios from 'axios'
import { bus } from '../bus'

const config = require('../../config')
const _      = require('lodash')

export default {
  name: 'Tracks',
  components: {},
  data: function () {
    return {
      tracks: [],
      count: 0,
      current: 1,
      perPage: 10,
      artist: '',
      title: '',
      queue: []
    }
  },
  created: function  () {
    this.getTracks(this.current)
    this.getQueue()
    this.debouncedGetTracks = _.debounce(this.getTracks, 1000)
    this.queueUpdateHandler = setInterval(() => this.getQueue(), 5000)
  },
  methods: {
    formatDuration: function (value) {
      var sec_num = parseInt(value, 10);
      var hours   = Math.floor(sec_num / 3600);
      var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
      var seconds = sec_num - (hours * 3600) - (minutes * 60);
      if (hours   < 10) {hours   = "0"+hours;}
      if (minutes < 10) {minutes = "0"+minutes;}
      if (seconds < 10) {seconds = "0"+seconds;}
      return hours+':'+minutes+':'+seconds;
    },
    resetFilters: function (event) {
      event.preventDefault()

      this.artist = ''
      this.title  = ''
      this.getTracks()
    },
    putTrackToQueue: function (track_id, event) {
      event.preventDefault()

      var self = this
      bus.$emit('app.loader', [true])

      axios.put(config.base_url + '/radio/queue', {
        track_id: track_id
      }).then(() => {
        self.$buefy.toast.open('Отправлено')
        self.getQueue()
        bus.$emit('app.loader', [false])
      }).catch((error) => {
        console.error(error)
        self.$buefy.toast.open('Произошла ошибка при заказе трека')
        bus.$emit('app.loader', [false])
      })
    },
    getQueue: function () {
      var self = this

      axios.get(config.base_url + '/radio/queue').then((response) => {
        self.queue = response.data.queue
      }).catch((error) => {
        console.error(error)
      })
    },
    getTracks: function (page) {
      var self = this
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
        self.tracks = response.data.tracks
        self.count  = response.data.count
      }).catch((error) => {
        console.log(error)
      })
    }
  },
  computed: {
    isTracksExist: function () {
      return this.tracks.length == 0 ? false : true
    }
  },
  watch: {
    'artist': function(to) {
      if (to.length < 3) {
        return
      }

      this.debouncedGetTracks()
    },
    'title': function (to) {
      if (to.length < 3) {
        return
      }

      this.debouncedGetTracks()
    }
  }
}
</script>

<style scoped>
h2 {
    font-size: 20px;
    text-align: center;
    margin: 20px;
}

.tracks-list {
    padding: 10px;
}

.queue-wrapper {
    margin-bottom: 10px;
}

.queue {
    padding: 10px;
    margin: 2px;
}
</style>
