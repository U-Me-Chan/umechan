import { createApp } from 'vue'

const app = createApp({})

// Simple event bus implementation for Vue 3
const handlers = {}

app.$on = (event, handler) => {
  if (!handlers[event]) handlers[event] = []
  handlers[event].push(handler)
}

app.$off = (event, handler) => {
  if (!handlers[event]) return
  if (handler) {
    handlers[event] = handlers[event].filter(h => h !== handler)
  } else {
    delete handlers[event]
  }
}

app.$emit = (event, ...args) => {
  if (!handlers[event]) return
  handlers[event].forEach(h => h(...args))
}

export const bus = app
