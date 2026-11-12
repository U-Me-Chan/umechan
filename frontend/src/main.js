import { createApp } from 'vue';
import App from './pages/App.vue';
import { createRouter, createWebHistory } from 'vue-router';
import Buefy from 'buefy';
import 'buefy/dist/css/buefy.css';
import '@mdi/font/css/materialdesignicons.min.css';
import './styles/theme.css';
import themeStore from './stores/theme';

themeStore.init();

const app = createApp(App);

app.config.productionTip = true;

app.use(Buefy);

const routes = [
  {
    path: '/',
    component: () => import('./pages/TOS.vue')
  },
  {
    path: '/thread/:id',
    component: () => import('./pages/Thread.vue')
  },
  {
    path: '/board/:tag',
    component: () => import('./pages/Board.vue')
  },
  {
    path: '/admin/files',
    component: () => import('./pages/Files.vue'),
    name: 'FilesPage',
    beforeEnter: (to, from, next) => {
      console.log(to);
      next()
    }
  },
  {
    path: '/admin/files/:page',
    name: 'FilesConcretePage',
    component: () => import('./pages/Files.vue')
  },
  {
    path: '/login',
    component: () => import('./pages/AdminCredentials.vue'),
    name: 'LoginPage'
  },
  {
    path: '/thread/:id/player',
    component: () => import('./pages/MediaPlayer.vue')
  },
  {
    path: '*',
    component: () => import('./pages/404.vue')
  }
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
});

app.use(router);

app.mount('#app');
