<template>
<div class="auth-panel">
  <div class="key-saved" v-if="isAdminKeySaved">
    <span class="admin-key-saved">Имеется ключ</span>
    <b-button type="is-light" @click="deleteAdminKey()">Удалить</b-button>
  </div>

  <div v-if="!isAdminKeySaved" class="form-save-key">
    <b-field label="Ключ администрирования">
      <b-input v-model="adminKey" placeholder="Введите ключ"></b-input>
    </b-field>
  
    <b-button type="is-info" @click="saveAdminKey()">Сохранить</b-button>
  </div>
</div>
</template>

<script>
export default {
  name: 'AuthPanel',
  data: function () {
    return {
      isAdminKeySaved: false,
      adminKey: ''
    }
  },
  created: function () {
    if (this.$cookie.get('admin_key') !== null) {
      this.isAdminKeySaved = true
    }
  },
  methods: {
    saveAdminKey: function () {
      this.$cookie.set('admin_key', this.adminKey, {samesite: 'strict'})
      this.isAdminKeySaved = true
    },
    deleteAdminKey: function () {
      this.$cookie.delete('admin_key')
      this.isAdminKeySaved = false
    }
  }
}
</script>

<style scoped>
.key-saved {
    display: flex;
    flex-direction: column;
}
</style>
