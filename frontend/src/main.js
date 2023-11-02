import Vue from 'vue'
import App from './components/App.vue'
import VueRouter from 'vue-router'
import {
  Toast,
  Sidebar,
  Menu,
  Button,
  Modal,
  Input,
  Field,
  Tag,
  Switch,
  Collapse,
  Pagination,
  Upload,
  Icon,
  Tooltip,
  Loading,
  Taginput
} from 'buefy'
import 'buefy/dist/buefy.css'

Vue.config.productionTip = true
Vue.use(Toast)
Vue.use(Sidebar)
Vue.use(Menu)
Vue.use(Button)
Vue.use(Modal)
Vue.use(Input)
Vue.use(Field)
Vue.use(Tag)
Vue.use(Switch)
Vue.use(Collapse)
Vue.use(Pagination)
Vue.use(Upload)
Vue.use(Icon)
Vue.use(Tooltip)
Vue.use(Loading)
Vue.use(Taginput)

Vue.use(VueRouter)

const routes = [
    {
      path: '/',
      component: () => import('./components/TOS.vue')
    },
    {
      path: '/thread/:id',
      component: () => import('./components/Thread.vue')
    },
    {
      path: '/board/:tag',
      component: () => import('./components/Board.vue')
    },
    {
      path: '/files',
      component: () => import('./components/Files.vue')
    }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

new Vue({
  router,
  render: h => h(App)
}).$mount('#app')
