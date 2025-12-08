<template>
<div>
  <b-input placeholder="Секрет владельца" v-model="key"/>
</div>
</template>

<script>
export default {
  name: 'AdminCredentials',
  data: function () {
    return {
      key: ''
    }
  },
  updated: function () {
    this.$cookie.set('admin_key', this.key)
  },
  created: function  () {
    this.key = this.$cookie.get('admin_key')
  }
}
</script>

<style scoped>
</style>
