<template>
<div>
  <b-input placeholder="Секрет владельца" v-model="key"/>
</div>
</template>

<script>
import { getAdminKey, setAdminKey } from '../utils/storage'

export default {
  name: 'AdminCredentials',
  data: function () {
    return {
      key: ''
    }
  },
  updated: function () {
    setAdminKey(this.key);
  },
  created: function  () {
    this.key = getAdminKey();
  }
}
</script>

<style scoped>
</style>
