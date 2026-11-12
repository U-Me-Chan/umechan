import { getCurrentInstance } from 'vue';

export function useToast() {
  const instance = getCurrentInstance();
  if (instance && instance.proxy && instance.proxy.$buefy) {
    return instance.proxy.$buefy.toast;
  }
  return null;
}

export function showToast(message, options = {}) {
  const toast = useToast();
  if (toast) {
    toast.open({
      message,
      ...options
    });
  }
}
