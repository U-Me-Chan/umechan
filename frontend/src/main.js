import Vue from 'vue';
import App from './pages/App.vue';
import VueRouter from 'vue-router';
import VueCookie from 'vue-cookie';

import {
  Toast,
  Sidebar,
  Menu,
  Button,
  Modal,
  Input,
  Field,
  Select,
  Tag,
  Table,
  Switch,
  Collapse,
  Pagination,
  Upload,
  Icon,
  Tooltip,
  Loading,
  Taginput
} from 'buefy';

import 'buefy/dist/buefy.css';
import '@mdi/font/css/materialdesignicons.min.css';

Vue.config.productionTip = true;
Vue.use(VueCookie);
Vue.use(Toast);
Vue.use(Sidebar);
Vue.use(Menu);
Vue.use(Button);
Vue.use(Modal);
Vue.use(Input);
Vue.use(Field);
Vue.use(Select);
Vue.use(Table);
Vue.use(Tag);
Vue.use(Switch);
Vue.use(Collapse);
Vue.use(Pagination);
Vue.use(Upload);
Vue.use(Icon);
Vue.use(Tooltip);
Vue.use(Loading);
Vue.use(Taginput);

Vue.use(VueRouter);

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
      component: () => import('./pages/Files.vue')
    },
    {
      path: '/admin/files/:page',
      component: () => import('./pages/Files.vue')
    },
    {
      path: '/tracks',
      component: () => import('./pages/Tracks.vue')
    },
    {
      path: '/admin/delete-post/:id',
      component: () => import('./pages/DeletePost.vue')
    }
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
});

new Vue({
  router,
  render: h => h(App)
}).$mount('#app');
