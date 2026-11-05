<template>
<div class="file card">
  <a :href="original" target="_blank"><img :src="thumbnail"></a>
  <a :href="`/thread/${id}`" target="_blank" v-for="id in postIds" :key="id">Post #{{id}}</a>
  <span v-if="postIds.length === 0">Нет постов</span>
  <span class="delete-btn" @click="deleteFile()">delete</span>
</div>
</template>

<script>
import { deleteFile } from '../../api/files';
import { bus } from '../../bus';

export default {
  name: 'File',
  props: {
    name: String,
    original: String,
    thumbnail: String,
    postIds: Array
  },
  methods: {
    deleteFile() {
      let key = '';

      if (localStorage.getItem('admin_key') !== null) {
        key = localStorage.getItem('admin_key');
      } else {
        key = prompt('Admin key', ['']);
      }

      deleteFile(this.name, {
        data: {},
        headers: {
          'Key': key
        }
      }).then((response) => {
        bus.$emit('files.file.deleted', [response]);
      }).catch((error) => {
        console.log(error);
        alert('Произошла ошибка при удалении файла');
      });
    }
  }
}
</script>

<style scoped>
.card {
    margin: 10px;
    padding: 5px;
    background-color: var(--color-card-bg);
}

img {
    width: 200px;
}

.file {
    display: flex;
    flex-direction: column;
}

.delete-btn {
    cursor: pointer;
    color: var(--color-link);
    text-decoration: underline;
}

.delete-btn:hover {
    color: var(--color-link-hover);
}
</style>
