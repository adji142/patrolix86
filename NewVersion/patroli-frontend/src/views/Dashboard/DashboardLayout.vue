<template>
  <div class="layout">
    <!-- Mobile overlay -->
    <Transition name="fade">
      <div v-if="sidebarOpen" class="overlay" @click="sidebarOpen = false" />
    </Transition>

    <!-- ===== Sidebar ===== -->
    <aside :class="['sidebar', { 'sidebar-open': sidebarOpen }]">
      <!-- Header -->
      <div class="sb-header">
        <div class="sb-logo-icon">
          <ShieldCheckIcon class="icon-md icon-white" />
        </div>
        <div class="sb-brand">
          <span class="sb-brand-name">Security Patrol</span>
          <span class="sb-partner">{{ namaPartner }}</span>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="sb-nav">
        <template v-for="menu in menus" :key="menu.id">
          <!-- Parent with children -->
          <template v-if="menu.children && menu.children.length > 0">
            <button
              class="sb-item sb-parent"
              :class="{ 'sb-item-expanded': expandedMenus.includes(menu.id) }"
              @click="toggleMenu(menu.id)"
            >
              <component :is="getMenuIcon(menu.ico, menu.id)" class="sb-item-icon" :size="16" :stroke-width="2" />
              <span class="sb-item-label">{{ menu.name }}</span>
              <ChevronDownIcon
                class="sb-chevron"
                :class="{ 'sb-chevron-up': expandedMenus.includes(menu.id) }"
              />
            </button>
            <div v-show="expandedMenus.includes(menu.id)" class="sb-children">
              <RouterLink
                v-for="child in menu.children"
                :key="child.id"
                :to="normalizeLink(child.link)"
                class="sb-child"
                active-class="sb-child-active"
              >
                <Dot class="sb-child-dot" :size="18" />
                {{ child.name }}
              </RouterLink>
            </div>
          </template>

          <!-- Standalone item -->
          <RouterLink
            v-else
            :to="normalizeLink(menu.link)"
            class="sb-item"
            active-class="sb-item-active"
          >
            <component :is="getMenuIcon(menu.ico, menu.id)" class="sb-item-icon" :size="16" :stroke-width="2" />
            <span class="sb-item-label">{{ menu.name }}</span>
          </RouterLink>
        </template>

        <div v-if="!menus.length && !isLoading" class="sb-empty">
          Tidak ada menu tersedia
        </div>
      </nav>

      <!-- User footer -->
      <div class="sb-user">
        <div class="avatar">{{ userInitial }}</div>
        <div class="sb-user-info">
          <span class="sb-user-name">{{ namaUser }}</span>
          <span class="sb-username">{{ username }}</span>
        </div>
        <button class="sb-logout" title="Keluar" @click="handleLogout">
          <ArrowRightOnRectangleIcon class="icon-base" />
        </button>
      </div>
    </aside>

    <!-- ===== Main Area ===== -->
    <div class="content-wrap">
      <!-- Mobile top header -->
      <header class="topbar">
        <button class="hamburger" @click="sidebarOpen = !sidebarOpen">
          <Bars3Icon class="icon-lg" />
        </button>
        <div class="topbar-brand">
          <div class="topbar-icon">
            <ShieldCheckIcon class="icon-sm icon-white" />
          </div>
          <span>Security Patrol</span>
        </div>
        <div class="topbar-user">
          <div class="avatar avatar-sm">{{ userInitial }}</div>
        </div>
      </header>

      <!-- Page content -->
      <main class="main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import {
  ShieldCheckIcon,
  ChevronDownIcon,
  Bars3Icon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'
import { Dot } from 'lucide-vue-next'
import { useAuthStore } from '../../stores/auth.js'
import { resolveMenuIcon } from '../../utils/icons.js'

export default {
  name: 'DashboardLayout',
  components: { ShieldCheckIcon, ChevronDownIcon, Bars3Icon, ArrowRightOnRectangleIcon, Dot },

  data() {
    return {
      sidebarOpen: false,
      expandedMenus: [],
    }
  },

  computed: {
    ...mapState(useAuthStore, ['user', 'menus', 'namaUser', 'namaPartner', 'username', 'isLoading']),
    userInitial() {
      return this.namaUser ? this.namaUser.charAt(0).toUpperCase() : 'U'
    },
  },

  async created() {
    const authStore = useAuthStore()
    if (!authStore.user) {
      await authStore.fetchUser()
    }
  },

  methods: {
    ...mapActions(useAuthStore, ['logout']),

    getMenuIcon(ico, id) {
      return resolveMenuIcon(ico, id)
    },

    toggleMenu(id) {
      const idx = this.expandedMenus.indexOf(id)
      if (idx >= 0) this.expandedMenus.splice(idx, 1)
      else this.expandedMenus.push(id)
    },

    normalizeLink(link) {
      if (!link || link === '#' || link.startsWith('http')) return '/app'
      return link.startsWith('/') ? link : `/${link}`
    },

    async handleLogout() {
      await this.logout()
      this.$router.push('/login')
    },
  },
}
</script>