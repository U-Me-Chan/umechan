<template>
<div class="file-list">
  <h1>Files</h1>

  <b-pagination
    :total="count"
    :current-page="current"
    :per-page="perPage"
    :page-input="true"
    v-model="current"
    v-on:change="changePage"
    order="is-centered"
    size="is-small"
    >
  </b-pagination>

  <div class="files">
    <File
      :name="file.name"
      :original="file.original"
      :thumbnail="file.thumbnail"
      :postIds="file.post_ids"
      v-for="file in files"
      :key="file.name"
      />
  </div>

    <b-pagination
    :total="count"
    :current-page="current"
    :per-page="perPage"
    :page-input="true"
    v-model="current"
    v-on:change="changePage"
    order="is-centered"
    size="is-small"
    >
  </b-pagination>
</div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { bus } from '../bus'
import File from '../components/Admin/File.vue'
import { getFiles } from '../api/files'

export default {
  components: {
    File
  },
  setup() {
    const router = useRouter();
    const route = useRoute();

    const files = ref([]);
    const count = ref(0);
    const current = ref(1);
    const perPage = ref(6);

    const changePage = function (page) {
      router.push('/admin/files/' + page);
    };

    const getFilesFn = function (page) {
      bus.$emit('app.loader', [true]);

      var offset = (page - 1) * perPage.value;

      getFiles({
        offset: offset,
        limit: perPage.value
      }).then((response) => {
        files.value = response.data.files;
        count.value = response.data.count;
        bus.$emit('app.loader', [false]);
      }).catch((error) => {
        console.log(error);
        bus.$emit('app.loader', [false]);
      });
    };

    let filesFileDeletedHandler;

    onMounted(() => {
      getFilesFn(current.value);

      filesFileDeletedHandler = () => {
        getFilesFn(current.value);
      };

      bus.$on('files.file.deleted', filesFileDeletedHandler);
    });

    watch(() => route.path, () => {
      getFilesFn(current.value);
    });

    return {
      files,
      count,
      current,
      perPage,
      changePage,
      getFiles: getFilesFn
    };
  }
}
</script>

<style scoped>
h1 {
    font-size: 40px;
    text-align: center;
    margin: 20px;
    color: var(--color-text);
}

.files {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
}

.theme-dark .file-list .box {
    background-color: var(--color-card-bg);
    color: var(--color-text);
}
</style>
