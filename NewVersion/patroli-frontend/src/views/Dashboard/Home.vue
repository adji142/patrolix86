<template>
  <div>
    <div class="top-bar">
      <h1 class="page-title">Dashboard</h1>
    </div>

    <div class="card welcome-card">
      <div class="welcome-header">
        <div class="welcome-icon">
          <ShieldCheckIcon class="icon-xl icon-blue" />
        </div>
        <div>
          <h2 class="welcome-name">Selamat datang, {{ namaUser || 'User' }}!</h2>
          <p class="welcome-partner">{{ namaPartner }}</p>
        </div>
      </div>
      <p class="welcome-desc">
        Gunakan menu di sebelah kiri untuk mengakses fitur sistem manajemen patroli security.
      </p>
    </div>
  </div>
</template>

<script>
import { mapState } from 'pinia'
import { ShieldCheckIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '../../stores/auth.js'

export default {
  name: 'DashboardHome',
  components: { ShieldCheckIcon },
  computed: {
    ...mapState(useAuthStore, ['namaUser', 'namaPartner']),
  },
}
</script>