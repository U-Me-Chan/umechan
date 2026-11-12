<template>
<div class="file card">
  <a :href="original" target="_blank"><img :src="thumbnail"></a>
  <a :href="`/thread/${id}`" target="_blank" v-for="id in postIds" :key="id">Post #{{id}}</a>
  <span v-if="postIds.length === 0">Нет постов</span>
  <span @click="deleteFile()">delete</span>
</div>
</template>

<script>
import { useRouter } from 'vue-router';

import { deleteFile } from '../../api/files'
import { bus } from '../../bus'
import { showToast } from '../../utils/toast'

export default {
  name: 'File',
  props: {
    name: String,
    original: String,
    thumbnail: String,
    postIds: Array
  },
  setup(props) {
    const router = useRouter();

    const selectThread = function (id) {
      router.push('/thread/' + id);
    };

    const deleteFileFn = function () {
      var key = '';

      const adminKey = document.cookie.split('; ').find(row => row.startsWith('umechan-admin_key='))?.split('=')[1];
      if (adminKey) {
        key = adminKey;
      } else {
        key = prompt('Admin key', ['']);
      }

      deleteFile(props.name, {
        data: {},
        headers: {
          'Key': key
        }
      }).then((response) => {
        bus.$emit('files.file.deleted', [response]);
      }).catch((error) => {
        console.log(error);
        showToast({ message: 'Произошла ошибка при удалении файла', type: 'is-danger' });
      });
    };

    return {
      selectThread,
      deleteFile: deleteFileFn
    };
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
</style>
