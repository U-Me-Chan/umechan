<template>
<div class="file-list">
  <h1>Files</h1>

  <div class="board-paginator">
    <PaginationComponent
      :total="count"
      :current="current"
      :per-page="perPage"
      @change="changePage"
    />
  </div>

  <div class="files">
    <FileComponent
      :name="file.name"
      :original="file.original"
      :thumbnail="file.thumbnail"
      :postIds="file.post_ids"
      v-for="file in files"
      :key="file.name"
    />
  </div>

  <div class="board-paginator">
    <PaginationComponent
      :total="count"
      :current="current"
      :per-page="perPage"
      @change="changePage"
    />
  </div>
</div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { bus } from '../bus';
import FileComponent from '../components/Admin/File.vue';
import PaginationComponent from '../components/Pagination.vue';
import { getFiles } from '../api/files';

export default {
  components: {
    FileComponent, PaginationComponent
  },
  setup() {
    const router = useRouter();
    const route = useRoute();

    const files = ref([]);
    const count = ref(0);
    const current = ref(1);
    const perPage = ref(6);

    let filesDeletedUnsub = null;

    const changePage = (page) => {
      router.push('/admin/files/' + page);
    };

    const init = () => {
      getFilesData(current.value);
    };

    const getFilesData = (page) => {
      bus.$emit('app.loader', [true]);

      const offset = page - 1;
      const offsetValue = offset * perPage.value;

      getFiles({
        offset: offsetValue,
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

    onMounted(() => {
      init();

      filesDeletedUnsub = bus.$on('files.file.deleted', function () {
        getFilesData(current.value);
      });
    });

    return {
      files,
      count,
      current,
      perPage,
      changePage,
      init
    };
  },
  beforeUnmount() {
    // clean up
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
