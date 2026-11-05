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
  Taginput,
  Dropdown
} from 'buefy';

import 'buefy/dist/buefy.css';
import '@mdi/font/css/materialdesignicons.min.css';
import './styles/theme.css';
import themeStore from './stores/theme';

Vue.config.productionTip = true;

themeStore.init();

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
Vue.use(Dropdown);

Vue.use(VueRouter);

// Lazy route loading with webpack chunk names for better code splitting
const load = (page) => () => import(/* webpackChunkName: "[request]" */ `./pages/${page}.vue`);

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
      next()
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

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
});

new Vue({
  router,
  render: h => h(App)
}).$mount('#app');
