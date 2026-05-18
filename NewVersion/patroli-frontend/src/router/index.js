import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/app' },

    {
      path: '/login',
      name: 'Login',
      component: () => import('../views/Auth/Login.vue'),
      meta: { guest: true },
    },

    {
      path: '/app',
      component: () => import('../views/Dashboard/DashboardLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Dashboard',
          component: () => import('../views/Dashboard/Home.vue'),
        },
        {
          path: 'lokasi-patroli',
          name: 'LokasiPatroli',
          component: () => import('../views/Dashboard/LokasiPatroli.vue'),
        },
        {
          path: 'master-security',
          name: 'MasterSecurity',
          component: () => import('../views/Dashboard/MasterSecurity.vue'),
        },
        {
          path: 'titik-patroli',
          name: 'TitikPatroli',
          component: () => import('../views/Dashboard/TitikPatroli.vue'),
        },
        {
          path: 'master-security/:nik/jadwal',
          name: 'JadwalSecurity',
          component: () => import('../views/Dashboard/JadwalSecurity.vue'),
        },
        {
          path: 'master-user',
          name: 'MasterUser',
          component: () => import('../views/Dashboard/MasterUser.vue'),
        },
        {
          path: 'review-patroli',
          name: 'ReviewPatroli',
          component: () => import('../views/Dashboard/ReviewPatroli.vue'),
        },
        {
          path: 'review-daily-activity',
          name: 'ReviewDailyActivity',
          component: () => import('../views/Dashboard/ReviewDailyActivity.vue'),
        },
        {
          path: 'review-bukutamu',
          name: 'ReviewBukuTamu',
          component: () => import('../views/Dashboard/ReviewBukuTamu.vue'),
        },
        {
          path: 'review-absensi',
          name: 'ReviewAbsensi',
          component: () => import('../views/Dashboard/ReviewAbsensi.vue'),
        },
        {
          path: 'review-absensi/karyawan',
          name: 'ReviewAbsensiKaryawan',
          component: () => import('../views/Dashboard/ReviewAbsensiKaryawan.vue'),
        },
        {
          path: 'review-pengajuan-izin',
          name: 'ReviewPengajuanIzin',
          component: () => import('../views/Dashboard/ReviewPengajuanIzin.vue'),
        },
        {
          path: 'review-pengajuan-cuti',
          name: 'ReviewPengajuanCuti',
          component: () => import('../views/Dashboard/ReviewPengajuanCuti.vue'),
        },
        {
          path: 'review-tukar-jadwal',
          name: 'ReviewTukarJadwal',
          component: () => import('../views/Dashboard/ReviewTukarJadwal.vue'),
        },
      ],
    },
  ],
})

router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else if (to.meta.guest && token) {
    next('/app')
  } else {
    next()
  }
})

export default router