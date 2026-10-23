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
        path: 'new-offer',
        name: 'new-offer',
        component: () => import('@/views/NewOfferView.vue'),
      },
    ],
  },
]

export default routes
