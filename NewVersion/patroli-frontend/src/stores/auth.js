import { defineStore } from 'pinia'
import axios from '../axios.js'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    isLoading: false,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    menus:      (state) => state.user?.menus      || [],
    namaUser:   (state) => state.user?.NamaUser   || '',
    namaPartner:(state) => state.user?.NamaPartner|| '',
    username:   (state) => state.user?.username   || '',
  },

  actions: {
    async login(credentials) {
      this.isLoading = true
      try {
        const res = await axios.post('/auth/login', credentials)
        if (res.data.success) {
          this.token = res.data.token
          localStorage.setItem('token', res.data.token)
          return { success: true }
        }
        return { success: false, message: res.data.message }
      } catch (e) {
        return {
          success: false,
          message: e.response?.data?.message || 'Terjadi kesalahan, coba lagi.',
        }
      } finally {
        this.isLoading = false
      }
    },

    async fetchUser() {
      try {
        const res = await axios.get('/auth/me')
        if (res.data.success) {
          this.user = res.data
        }
      } catch {
        this.clearAuth()
      }
    },

    async logout() {
      try { await axios.post('/auth/logout') } catch {}
      this.clearAuth()
    },

    clearAuth() {
      this.token = null
      this.user  = null
      localStorage.removeItem('token')
      localStorage.removeItem('activeBranch')
    },
  },
})