import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  base: '/',
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
  },
  server: {
    port: 8080,
    proxy: {
      '/v2': {
        target: 'http://localhost:3000',
        changeOrigin: true,
      },
      '/_': {
        target: 'http://localhost:3000',
        changeOrigin: true,
      },
    },
  },
});
