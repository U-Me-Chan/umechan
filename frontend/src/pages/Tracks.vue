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
        :current-page="current"
        v-model:current-page="current"
        backend-pagination
        :total="count"
        @page-change="getTracks">

        <b-table-column field="artist" label="Исполнитель" searchable sortable>
          <template #searchable>
            <b-input v-model="artist" placeholder="Найти исполнителя"/>
          </template>
          <template v-slot="props">{{props.row.artist}}</template>
        </b-table-column>

        <b-table-column field="title" label="Композиция" searchable sortable>
          <template #searchable>
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
import { ref, computed, onMounted, watch } from 'vue';

import { bus } from '../bus'
import { putTrackToQueue as apiPutTrackToQueue, getQueue, getTrackList } from '../api/radio'
import { formatDuration } from '../utils/duration_formatter'
import { showToast } from '../utils/toast'
import _ from 'lodash'

export default {
  name: 'Tracks',
  components: {},
  setup() {
    const tracks = ref([]);
    const count = ref(0);
    const current = ref(1);
    const perPage = ref(10);
    const artist = ref('');
    const title = ref('');
    const queue = ref([]);

    const isTracksExist = computed(() => tracks.value.length > 0);

    const getTracks = function (page) {
      var offset = (page - 1) * perPage.value;

      getTrackList(offset, perPage.value, artist.value, title.value).then((response) => {
        tracks.value = response.data.tracks;
        count.value = response.data.count;
      }).catch((error) => {
        console.log(error);
      });
    };

    const getQueueFn = function () {
      getQueue().then((response) => {
        queue.value = response.data.queue;
      }).catch((error) => {
        console.error(error);
      });
    };

    const resetFilters = function (event) {
      event.preventDefault();
      artist.value = '';
      title.value = '';
      getTracks(current.value);
    };

    const putTrackToQueueFn = function (track_id, event) {
      event.preventDefault();
      bus.$emit('app.loader', [true]);

      apiPutTrackToQueue(track_id).then(() => {
        showToast({ message: 'Отправлено', type: 'is-success' });
        getQueueFn();
        bus.$emit('app.loader', [false]);
      }).catch((error) => {
        console.error(error);
        showToast({ message: 'Произошла ошибка при заказе трека', type: 'is-danger' });
        bus.$emit('app.loader', [false]);
      });
    };

    const formatDurationFn = function (value) {
      return formatDuration(value);
    };

    let debouncedGetTracks;

    onMounted(() => {
      getTracks(current.value);
      getQueueFn();
      debouncedGetTracks = _.debounce(() => getTracks(current.value), 1000);
      setInterval(() => getQueueFn(), 5000);
    });

    watch(artist, (to) => {
      if (to.length < 3) return;
      debouncedGetTracks();
    });

    watch(title, (to) => {
      if (to.length < 3) return;
      debouncedGetTracks();
    });

    return {
      tracks,
      count,
      current,
      perPage,
      artist,
      title,
      queue,
      isTracksExist,
      formatDuration: formatDurationFn,
      resetFilters,
      putTrackToQueue: putTrackToQueueFn,
      getTracks
    };
  }
}
</script>

<style scoped>
h2 {
    font-size: 20px;
    text-align: center;
    margin: 20px;
    color: var(--color-text);
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
    background-color: var(--color-card-bg);
    color: var(--color-text);
}

.theme-dark .tracks-list .box {
    background-color: var(--color-card-bg);
    color: var(--color-text);
}
</style>
