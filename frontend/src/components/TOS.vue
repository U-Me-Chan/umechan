<template>
<div class="tos">
  <h1> U III E</h1>

  <div class="content">
    <section class="hero" v-for="board in boards" :key="board.id">
      <div class="hero-body">
        <p class="title"><a :href="'/board/' + board.tag">{{ board.name }}</a></p>
        <p>
          Новых постов за день: {{ board.new_posts_count }}
        </p>
        <p>
          Всего тредов: {{ board.threads_count }}
        </p>
      </div>
    </section>
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
      boards: []
    }
  },
  created: function  () {
    var self = this
    
    axios.get(config.chan_url + '/v2/board').then((response) => {
      self.boards = response.data.payload.boards
      bus.global.boards = response.data.payload.boards
    }).catch((error) => {
      console.log(error)
    })
  }
}
</script>

<style scoped>
h1 {
    font-size: 40px;
    text-align: center;
    margin: 20px;
}

.hero {
    background-color: #fff;
    border-radius: 3px;
    margin: 1px;
}
</style>
