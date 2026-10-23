import OffersView from '@/views/OffersView.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'main',
    component: MainLayout,
    children: [
      {
        path: '',
        name: 'offers',
        component: OffersView,
      },
      {
        path: 'offer-form/:id?',
        name: 'offer-form',
        component: () => import('@/views/OfferFormView.vue'),
      },
    ],
  },
]

export default routes
