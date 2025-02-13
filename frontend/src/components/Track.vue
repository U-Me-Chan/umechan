<template>
<div class="track card">
  <b-button @click="putTrackToQueue(id, $event)" size="is-small">В очередь</b-button>
  <span class="track-info">{{artist}} - {{title}}</span>
</div>
</template>

<script>
import axios from 'axios'
import { bus} from '../bus'

const config = require('../../config')

export default {
  name: 'Track',
  props: {
    id: Number,
    artist: String,
    title: String
  },
  methods: {
    putTrackToQueue: function (track_id, event) {
      event.preventDefault()
      
      var self = this
      bus.$emit('app.loader', [true])

      axios.put(

        config.base_url + '/radio/queue', {
          track_id: track_id
        }
      ).then(() => {
        self.$buefy.toast.open('Отправлено!')
        bus.$emit('app.loader', [false])
      }).catch((error) => {
        self.$buefy.toast.open('Произошла ошибка при заказе трека')
        console.error(error)
        bus.$emit('app.loader', [false])
      }) 
    }
  }
}
</script>

<style scoped>
.track {
    padding: 5px;
    margin: 2px;
    font-size: 14px;
}

.track-info {
    padding: 10px;
}
</style>
