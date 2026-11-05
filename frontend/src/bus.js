// Simple event bus for Vue 3 (Vue 3 doesn't have $on/$off built-in)
export const bus = {
  events: {},

  $on(event, handler) {
    if (!this.events[event]) {
      this.events[event] = [];
    }
    this.events[event].push(handler);
    return () => this.$off(event, handler);
  },

  $off(event, handler) {
    if (!this.events[event]) return;
    if (!handler) {
      this.events[event] = [];
    } else {
      this.events[event] = this.events[event].filter((h) => h !== handler);
    }
  },

  $emit(event, ...args) {
    if (this.events[event]) {
      this.events[event].forEach((handler) => handler(...args));
    }
  },

  $once(event, handler) {
    const wrapped = (...args) => {
      handler(...args);
      this.$off(event, wrapped);
    };
    this.$on(event, wrapped);
  }
};
