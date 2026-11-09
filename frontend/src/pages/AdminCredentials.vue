<template>
<div class="admin-credentials">
  <b-input placeholder="Секрет владельца" v-model="key"/>
</div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { setAdminKey, getAdminKey } from '../utils/storage'

export default {
  name: 'AdminCredentials',
  setup() {
    const key = ref('');

    onMounted(() => {
      setAdminKey(key.value);
    });

    key.value = getAdminKey();

    return {
      key
    };
  }
}
</script>

<style scoped>
.admin-credentials {
    max-width: 400px;
    margin: 40px auto;
}
</style>
