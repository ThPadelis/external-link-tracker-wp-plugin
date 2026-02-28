import { createApp } from 'vue'
import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query'
import './style.css'
import App from './App.vue'

const app = createApp(App)
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 2,
    },
  },
})

app.use(VueQueryPlugin, { queryClient })
app.mount('#elt-admin-app')
