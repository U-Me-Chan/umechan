import { createApp } from 'vue';
import App from './pages/App.vue';
import { createRouter, createWebHistory } from 'vue-router';

import 'core-js';
import '@mdi/font/css/materialdesignicons.min.css';
import './styles/theme.css';
import themeStore from './stores/theme';

themeStore.init();

const app = createApp(App);

// Lazy route loading
const load = (page) => () => import(`./pages/${page}.vue`);

const routes = [
  {
    path: '/',
    component: load('TOS')
  },
  {
    path: '/thread/:id',
    component: load('Thread')
  },
  {
    path: '/board/:tag',
    component: load('Board')
  },
  {
    path: '/admin/files',
    component: load('Files'),
    name: 'FilesPage',
    beforeEnter: (to, from, next) => {
      console.log(to);
      next();
    }
  },
  {
    path: '/admin/files/:page',
    name: 'FilesConcretePage',
    component: load('Files')
  },
  {
    path: '/login',
    component: load('AdminCredentials'),
    name: 'LoginPage'
  },
  {
    path: '/thread/:id/player',
    component: load('MediaPlayer')
  },
  {
    path: '*',
    component: load('404')
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
});

app.use(router);
app.mount('#app');
