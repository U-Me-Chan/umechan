<template>
<div class="admin-credentials">
  <input
    class="input"
    type="text"
    placeholder="Секрет владельца"
    v-model="key"
    @input="onKeyInput"
  />
</div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { getAdminKey, setAdminKey } from '../utils/storage';

export default {
  name: 'AdminCredentials',
  setup() {
    const key = ref('');

    let inputHandler = null;

    const onKeyInput = (e) => {
      setAdminKey(e.target.value);
    };

    onMounted(() => {
      key.value = getAdminKey() || '';
    });

    return {
      key,
      onKeyInput
    };
  }
}
</script>

<style scoped>
.admin-credentials {
    max-width: 400px;
    margin: 40px auto;
}

.input {
    width: 100%;
    padding: 8px;
    border: 1px solid var(--color-input-border);
    background-color: var(--color-input-bg);
    color: var(--color-input-text);
    border-radius: 4px;
}
</style>
