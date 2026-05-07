<template>
  <div class="login-wrapper">
    <!-- Left: Branding panel -->
    <div class="login-left">
      <div class="login-branding">
        <div class="brand-icon">
          <ShieldCheckIcon class="icon-brand icon-white" />
        </div>
        <h1>Security Patrol</h1>
        <p>Sistem Manajemen Patroli Security Terpadu</p>

        <div class="feature-list">
          <div v-for="item in features" :key="item" class="feature-item">
            <CheckCircleIcon class="icon-base shrink-0" />
            <span>{{ item }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Form -->
    <div class="login-right">
      <div class="login-form-wrap">
        <!-- Mobile-only brand -->
        <div class="mobile-brand">
          <div class="mobile-brand-icon">
            <ShieldCheckIcon class="icon-2xl icon-white" />
          </div>
          <span>Security Patrol</span>
        </div>

        <h2 class="form-title">Selamat Datang</h2>
        <p class="form-subtitle">Masuk ke panel manajemen patroli</p>

        <!-- Error alert -->
        <div v-if="errorMsg" class="error-alert">
          <ExclamationCircleIcon class="icon-base shrink-0" />
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleLogin">
          <div class="form-field">
            <label>Kode Partner</label>
            <input
              v-model="form.RecordOwnerID"
              class="input"
              type="text"
              placeholder="Contoh: PARTNER001"
              autocomplete="organization"
              required
            />
          </div>

          <div class="form-field">
            <label>Username</label>
            <input
              v-model="form.username"
              class="input"
              type="text"
              placeholder="Masukkan username"
              autocomplete="username"
              required
            />
          </div>

          <div class="form-field">
            <label>Password</label>
            <div class="field-input-wrap">
              <input
                v-model="form.password"
                class="input input-has-addon"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required
              />
              <button type="button" class="eye-btn" @click="showPassword = !showPassword">
                <EyeSlashIcon v-if="showPassword" class="icon-md" />
                <EyeIcon v-else class="icon-md" />
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary submit-btn" :disabled="loading">
            <ArrowPathIcon v-if="loading" class="spin-icon icon-base" />
            {{ loading ? 'Memproses...' : 'Masuk' }}
          </button>
        </form>

        <p class="footer-text">&copy; {{ year }} Security Patrol SaaS</p>
      </div>
    </div>
  </div>
</template>

<script>
import {
  ShieldCheckIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  EyeIcon,
  EyeSlashIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '../../stores/auth.js'

export default {
  name: 'LoginView',
  components: {
    ShieldCheckIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowPathIcon,
  },

  data() {
    return {
      form: {
        RecordOwnerID: '',
        username: '',
        password: '',
      },
      showPassword: false,
      errorMsg: '',
      loading: false,
      year: new Date().getFullYear(),
      features: [
        'Monitoring patroli real-time',
        'Laporan absensi otomatis',
        'Face recognition security',
        'Multi-lokasi & multi-shift',
      ],
    }
  },

  methods: {
    async handleLogin() {
      this.errorMsg = ''
      this.loading = true

      const authStore = useAuthStore()
      const result = await authStore.login({
        ...this.form,
        LoginDate: new Date().toISOString().replace('T', ' ').slice(0, 19),
      })

      if (result.success) {
        await authStore.fetchUser()
        this.$router.push('/app')
      } else {
        this.errorMsg = result.message
      }

      this.loading = false
    },
  },
}
</script>