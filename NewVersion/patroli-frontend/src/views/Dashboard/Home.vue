<template>
  <div class="db-root">

    <!-- ── Top Bar ── -->
    <div class="db-topbar">
      <div>
        <h1 class="db-title">Dashboard</h1>
        <p class="db-subtitle">{{ todayLabel }}</p>
      </div>
      <div class="db-topbar-right">
        <div class="db-welcome-badge">
          <UserCircleIcon class="db-welcome-icon" />
          <span>Selamat datang, <strong>{{ namaUser || 'User' }}</strong></span>
        </div>
      </div>
    </div>

    <!-- ── Filter Bar ── -->
    <div class="db-filter-bar">
      <div class="db-filter-left">
        <MapPinIcon class="db-filter-icon" />
        <label class="db-filter-label">Lokasi</label>
        <select v-model="selectedLokasi" class="db-filter-select">
          <option value="">— Semua Lokasi —</option>
          <option
            v-for="lok in lokasiAkses"
            :key="lok.id"
            :value="lok.id"
          >{{ lok.NamaArea }}</option>
        </select>
      </div>
      <div class="db-filter-right" v-if="selectedLokasiObj">
        <span class="db-filter-chip">
          <MapPinIcon class="db-filter-chip-icon" />
          {{ selectedLokasiObj.NamaArea }}
          <span v-if="selectedLokasiObj.AlamatArea" class="db-filter-chip-addr">· {{ selectedLokasiObj.AlamatArea }}</span>
        </span>
      </div>
    </div>

    <!-- ── KPI Cards ── -->
    <div class="db-kpi-grid">
      <!-- Total Security -->
      <div class="db-kpi-card kpi-blue">
        <div class="kpi-icon-wrap kpi-icon-blue">
          <ShieldCheckIcon class="kpi-icon" />
        </div>
        <div class="kpi-body">
          <div class="kpi-value">
            <span v-if="kpiLoading" class="kpi-skeleton"></span>
            <span v-else>{{ kpiData.total_security ?? '-' }}</span>
          </div>
          <div class="kpi-label">Total Security Aktif</div>
        </div>
        <div class="kpi-trend kpi-trend-neutral">per hari ini</div>
      </div>

      <!-- Sudah Absen Masuk -->
      <div class="db-kpi-card kpi-green">
        <div class="kpi-icon-wrap kpi-icon-green">
          <CheckCircleIcon class="kpi-icon" />
        </div>
        <div class="kpi-body">
          <div class="kpi-value">
            <span v-if="kpiLoading" class="kpi-skeleton"></span>
            <span v-else>{{ kpiData.sudah_absen ?? '-' }}</span>
          </div>
          <div class="kpi-label">Sudah Absen Masuk</div>
        </div>
        <div class="kpi-trend" :class="kpiTrendClass(kpiData.sudah_absen, kpiData.total_security)">
          {{ kpiLoading ? '...' : absensiRateLabel }}
        </div>
      </div>

      <!-- Belum Absen -->
      <div class="db-kpi-card kpi-orange">
        <div class="kpi-icon-wrap kpi-icon-orange">
          <ExclamationCircleIcon class="kpi-icon" />
        </div>
        <div class="kpi-body">
          <div class="kpi-value">
            <span v-if="kpiLoading" class="kpi-skeleton"></span>
            <span v-else>{{ kpiData.belum_absen ?? '-' }}</span>
          </div>
          <div class="kpi-label">Belum Absen</div>
        </div>
        <div class="kpi-trend kpi-trend-neutral">dari jadwal hari ini</div>
      </div>

      <!-- Patroli -->
      <div class="db-kpi-card kpi-purple">
        <div class="kpi-icon-wrap kpi-icon-purple">
          <MapPinIcon class="kpi-icon" />
        </div>
        <div class="kpi-body">
          <div class="kpi-value">
            <span v-if="kpiLoading" class="kpi-skeleton"></span>
            <span v-else>{{ kpiData.patroli_berlangsung ?? '-' }}</span>
          </div>
          <div class="kpi-label">Patroli Hari Ini</div>
        </div>
        <div class="kpi-trend kpi-trend-neutral">total patroli hari ini</div>
      </div>
    </div>

    <!-- ── Main Grid (2 col) ── -->
    <div class="db-main-grid">

      <!-- LEFT: Absensi Bulan Ini + Patroli -->
      <div class="db-col-left">

        <!-- Absensi Stat Cards -->
        <div class="db-card">
          <div class="db-card-header">
            <div class="db-card-title-wrap">
              <ChartBarIcon class="db-card-icon" />
              <span class="db-card-title">Statistik Absensi — Bulan Ini</span>
            </div>
            <span class="db-badge db-badge-blue">{{ monthLabel }}</span>
          </div>
          <div class="absensi-stat-grid">
            <div class="absensi-stat-item stat-green">
              <CheckCircleIcon class="stat-icon" />
              <div class="stat-number">312</div>
              <div class="stat-label">Total Hadir</div>
            </div>
            <div class="absensi-stat-item stat-blue">
              <DocumentTextIcon class="stat-icon" />
              <div class="stat-number">14</div>
              <div class="stat-label">Total Izin</div>
            </div>
            <div class="absensi-stat-item stat-purple">
              <SunIcon class="stat-icon" />
              <div class="stat-number">8</div>
              <div class="stat-label">Total Cuti</div>
            </div>
            <div class="absensi-stat-item stat-red">
              <XCircleIcon class="stat-icon" />
              <div class="stat-number">6</div>
              <div class="stat-label">Tidak Absen</div>
            </div>
          </div>
          <!-- Progress bar hadir -->
          <div class="absensi-progress-wrap">
            <div class="absensi-progress-label">
              <span>Tingkat Kehadiran</span>
              <strong>91.8%</strong>
            </div>
            <div class="absensi-progress-track">
              <div class="absensi-progress-fill" style="width: 91.8%"></div>
            </div>
          </div>
        </div>

        <!-- Patroli Summary -->
        <div class="db-card">
          <div class="db-card-header">
            <div class="db-card-title-wrap">
              <MapIcon class="db-card-icon" />
              <span class="db-card-title">Rekap Patroli Hari Ini</span>
            </div>
            <RouterLink to="/app/review-patroli" class="db-link-btn">Lihat Detail →</RouterLink>
          </div>
          <div class="patroli-summary-grid">
            <div class="patroli-summary-item">
              <div class="patroli-area-name">Pos Utama</div>
              <div class="patroli-progress-row">
                <div class="patroli-track">
                  <div class="patroli-fill" style="width: 80%"></div>
                </div>
                <span class="patroli-pct">8/10</span>
              </div>
              <div class="patroli-status patroli-status-ok">● Berlangsung</div>
            </div>
            <div class="patroli-summary-item">
              <div class="patroli-area-name">Pos Belakang</div>
              <div class="patroli-progress-row">
                <div class="patroli-track">
                  <div class="patroli-fill patroli-fill-done" style="width: 100%"></div>
                </div>
                <span class="patroli-pct">5/5</span>
              </div>
              <div class="patroli-status patroli-status-done">● Selesai</div>
            </div>
            <div class="patroli-summary-item">
              <div class="patroli-area-name">Pos Gerbang</div>
              <div class="patroli-progress-row">
                <div class="patroli-track">
                  <div class="patroli-fill patroli-fill-warn" style="width: 33%"></div>
                </div>
                <span class="patroli-pct">2/6</span>
              </div>
              <div class="patroli-status patroli-status-warn">● Baru Mulai</div>
            </div>
          </div>
        </div>

      </div>

      <!-- RIGHT: Pending Approvals + Activity -->
      <div class="db-col-right">

        <!-- Pending Approvals -->
        <div class="db-card">
          <div class="db-card-header">
            <div class="db-card-title-wrap">
              <ClockIcon class="db-card-icon" />
              <span class="db-card-title">Menunggu Persetujuan</span>
            </div>
          </div>
          <div class="approval-list">
            <RouterLink to="/app/review-pengajuan-izin" class="approval-item approval-izin">
              <div class="approval-icon-wrap approval-icon-blue">
                <DocumentTextIcon class="approval-icon" />
              </div>
              <div class="approval-body">
                <div class="approval-title">Pengajuan Izin</div>
                <div class="approval-desc">3 pengajuan menunggu approval</div>
              </div>
              <div class="approval-badge approval-badge-blue">3</div>
              <ChevronRightIcon class="approval-arrow" />
            </RouterLink>

            <RouterLink to="/app/review-pengajuan-cuti" class="approval-item approval-cuti">
              <div class="approval-icon-wrap approval-icon-purple">
                <SunIcon class="approval-icon" />
              </div>
              <div class="approval-body">
                <div class="approval-title">Pengajuan Cuti</div>
                <div class="approval-desc">1 pengajuan menunggu approval</div>
              </div>
              <div class="approval-badge approval-badge-purple">1</div>
              <ChevronRightIcon class="approval-arrow" />
            </RouterLink>

            <RouterLink to="/app/review-tukar-jadwal" class="approval-item approval-tukar">
              <div class="approval-icon-wrap approval-icon-orange">
                <ArrowsRightLeftIcon class="approval-icon" />
              </div>
              <div class="approval-body">
                <div class="approval-title">Tukar Jadwal</div>
                <div class="approval-desc">2 permintaan menunggu approval</div>
              </div>
              <div class="approval-badge approval-badge-orange">2</div>
              <ChevronRightIcon class="approval-arrow" />
            </RouterLink>
          </div>
        </div>

        <!-- Aktivitas Terkini -->
        <div class="db-card">
          <div class="db-card-header">
            <div class="db-card-title-wrap">
              <BoltIcon class="db-card-icon" />
              <span class="db-card-title">Aktivitas Terkini</span>
            </div>
          </div>
          <div class="activity-feed">
            <div class="activity-item">
              <div class="activity-dot dot-green"></div>
              <div class="activity-content">
                <div class="activity-name">Budi Santoso</div>
                <div class="activity-desc">Check-in di Pos Utama</div>
              </div>
              <div class="activity-time">08:02</div>
            </div>
            <div class="activity-item">
              <div class="activity-dot dot-blue"></div>
              <div class="activity-content">
                <div class="activity-name">Rudi Hartono</div>
                <div class="activity-desc">Patroli selesai — Pos Belakang</div>
              </div>
              <div class="activity-time">07:55</div>
            </div>
            <div class="activity-item">
              <div class="activity-dot dot-orange"></div>
              <div class="activity-content">
                <div class="activity-name">Siti Aisyah</div>
                <div class="activity-desc">Pengajuan izin dikirim</div>
              </div>
              <div class="activity-time">07:30</div>
            </div>
            <div class="activity-item">
              <div class="activity-dot dot-green"></div>
              <div class="activity-content">
                <div class="activity-name">Ahmad Fauzi</div>
                <div class="activity-desc">Check-in di Pos Gerbang</div>
              </div>
              <div class="activity-time">07:15</div>
            </div>
            <div class="activity-item">
              <div class="activity-dot dot-red"></div>
              <div class="activity-content">
                <div class="activity-name">Deni Kusuma</div>
                <div class="activity-desc">Belum absen — jadwal pagi</div>
              </div>
              <div class="activity-time">07:00</div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '../../stores/auth.js'
import axios from '../../axios.js'
import {
  ShieldCheckIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  MapPinIcon,
  ChartBarIcon,
  DocumentTextIcon,
  XCircleIcon,
  MapIcon,
  ClockIcon,
  ChevronRightIcon,
  ArrowsRightLeftIcon,
  BoltIcon,
  UserCircleIcon,
  SunIcon,
} from '@heroicons/vue/24/outline'

export default {
  name: 'DashboardHome',
  components: {
    ShieldCheckIcon, CheckCircleIcon, ExclamationCircleIcon, MapPinIcon,
    ChartBarIcon, DocumentTextIcon, XCircleIcon, MapIcon, ClockIcon,
    ChevronRightIcon, ArrowsRightLeftIcon, BoltIcon, UserCircleIcon, SunIcon,
  },

  data() {
    return {
      selectedLokasi: '',
      kpiData: {},
      kpiLoading: false,
    }
  },

  computed: {
    ...mapState(useAuthStore, ['namaUser', 'namaPartner', 'user']),

    lokasiAkses() {
      return this.user?.lokasi_akses || []
    },

    selectedLokasiObj() {
      if (!this.selectedLokasi) return null
      return this.lokasiAkses.find(l => String(l.id) === String(this.selectedLokasi)) || null
    },

    absensiRateLabel() {
      const total = this.kpiData.total_security
      const hadir = this.kpiData.sudah_absen
      if (!total || total === 0) return 'dari total security'
      const pct = Math.round((hadir / total) * 100)
      return `${pct}% dari total security`
    },

    todayLabel() {
      const now = new Date()
      const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']
      const months = ['Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember']
      return `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`
    },

    monthLabel() {
      const now = new Date()
      const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']
      return `${months[now.getMonth()]} ${now.getFullYear()}`
    },
  },

  watch: {
    user: {
      immediate: true,
      handler(val) {
        if (!val || this.selectedLokasi) return
        const userLocationID = val.LocationID
        const akses = val.lokasi_akses || []
        if (userLocationID && akses.length) {
          const match = akses.find(l => String(l.id) === String(userLocationID))
          if (match) this.selectedLokasi = match.id
        }
      },
    },
    // Re-fetch KPI saat filter lokasi berubah
    selectedLokasi(val, old) {
      if (val !== old) this.fetchKpi()
    },
  },

  mounted() {
    this.fetchKpi()
  },

  methods: {
    kpiTrendClass(value, total) {
      if (!total || total === 0) return 'kpi-trend-neutral'
      const pct = (value / total) * 100
      if (pct >= 80) return 'kpi-trend-up'
      if (pct >= 50) return 'kpi-trend-neutral'
      return 'kpi-trend-down'
    },

    async fetchKpi() {
      this.kpiLoading = true
      try {
        const params = {}
        if (this.selectedLokasi) params.location_id = this.selectedLokasi
        const res = await axios.get('/dashboard/kpi', { params })
        if (res.data.success) {
          this.kpiData = res.data.data
        }
      } catch (e) {
        console.error('Gagal fetch KPI:', e)
      } finally {
        this.kpiLoading = false
      }
    },
  },
}
</script>

<style scoped>
/* ── Root ── */
.db-root {
  padding: 0;
  font-family: 'Inter', sans-serif;
}

/* ── Top Bar ── */
.db-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 16px;
}

/* ── Filter Bar ── */
.db-filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 10px 16px;
  margin-bottom: 22px;
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.db-filter-left {
  display: flex;
  align-items: center;
  gap: 8px;
}
.db-filter-icon {
  width: 17px; height: 17px;
  color: #0d2b5e;
  flex-shrink: 0;
}
.db-filter-label {
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  white-space: nowrap;
}
.db-filter-select {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 5px 30px 5px 10px;
  font-size: 13px;
  font-weight: 500;
  color: #0d2b5e;
  background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%230d2b5e' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center;
  -webkit-appearance: none;
  appearance: none;
  cursor: pointer;
  outline: none;
  min-width: 200px;
  transition: border-color .15s;
}
.db-filter-select:focus { border-color: #0d2b5e; }
.db-filter-select:disabled { opacity: .5; cursor: not-allowed; }
.db-filter-right {
  display: flex;
  align-items: center;
}
.db-filter-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #eff6ff;
  color: #0d2b5e;
  border: 1px solid #bfdbfe;
  border-radius: 99px;
  padding: 4px 12px;
  font-size: 12.5px;
  font-weight: 600;
}
.db-filter-chip-icon {
  width: 13px; height: 13px;
  color: #0d2b5e;
}
.db-filter-chip-addr {
  font-weight: 400;
  color: #64748b;
  font-size: 11.5px;
}

.db-title {
  font-size: 22px;
  font-weight: 700;
  color: #0d2b5e;
  margin: 0;
}
.db-subtitle {
  font-size: 13px;
  color: #64748b;
  margin: 2px 0 0;
}
.db-welcome-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 99px;
  padding: 7px 16px 7px 10px;
  font-size: 13px;
  color: #334155;
  box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.db-welcome-icon { width: 22px; height: 22px; color: #0d2b5e; }

/* ── KPI Grid ── */
.db-kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
.db-kpi-card {
  background: #fff;
  border-radius: 14px;
  padding: 20px 18px 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,.07);
  display: flex;
  flex-direction: column;
  gap: 4px;
  border-top: 4px solid transparent;
  transition: transform .15s, box-shadow .15s;
}
.db-kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.1); }
.kpi-blue  { border-top-color: #0d2b5e; }
.kpi-green { border-top-color: #16a34a; }
.kpi-orange{ border-top-color: #d97706; }
.kpi-purple{ border-top-color: #7c3aed; }

.kpi-icon-wrap {
  width: 44px; height: 44px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 10px;
}
.kpi-icon-blue   { background: #eff6ff; }
.kpi-icon-green  { background: #f0fdf4; }
.kpi-icon-orange { background: #fffbeb; }
.kpi-icon-purple { background: #f5f3ff; }
.kpi-icon { width: 22px; height: 22px; }
.kpi-icon-blue   .kpi-icon { color: #0d2b5e; }
.kpi-icon-green  .kpi-icon { color: #16a34a; }
.kpi-icon-orange .kpi-icon { color: #d97706; }
.kpi-icon-purple .kpi-icon { color: #7c3aed; }

.kpi-body { flex: 1; }
.kpi-value { font-size: 32px; font-weight: 800; color: #0f172a; line-height: 1; }
.kpi-label { font-size: 12.5px; color: #64748b; margin-top: 4px; font-weight: 500; }
.kpi-trend { font-size: 11.5px; font-weight: 600; margin-top: 8px; }
.kpi-trend-up      { color: #16a34a; }
.kpi-trend-neutral { color: #64748b; }
.kpi-trend-down    { color: #dc2626; }

/* Skeleton shimmer for KPI loading */
.kpi-skeleton {
  display: inline-block;
  width: 52px;
  height: 36px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite;
  border-radius: 6px;
  vertical-align: middle;
}
@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Main 2-col Grid ── */
.db-main-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 20px;
  align-items: start;
}
.db-col-left, .db-col-right { display: flex; flex-direction: column; gap: 20px; }

/* ── Card base ── */
.db-card {
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 10px rgba(0,0,0,.07);
  overflow: hidden;
}
.db-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
}
.db-card-title-wrap { display: flex; align-items: center; gap: 8px; }
.db-card-icon { width: 18px; height: 18px; color: #0d2b5e; }
.db-card-title { font-size: 14px; font-weight: 700; color: #0d2b5e; }
.db-badge {
  font-size: 11px; font-weight: 600;
  padding: 3px 10px; border-radius: 99px;
}
.db-badge-blue { background: #eff6ff; color: #0d2b5e; }
.db-link-btn {
  font-size: 12.5px; font-weight: 600; color: #0d2b5e;
  text-decoration: none;
  padding: 4px 12px; border-radius: 99px;
  background: #eff6ff;
  transition: background .15s;
}
.db-link-btn:hover { background: #dbeafe; }

/* ── Absensi Stats ── */
.absensi-stat-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  padding: 16px 20px;
}
.absensi-stat-item {
  border-radius: 10px;
  padding: 14px 10px;
  text-align: center;
  border: 1px solid transparent;
}
.stat-green  { background: #f0fdf4; border-color: #bbf7d0; }
.stat-blue   { background: #eff6ff; border-color: #bfdbfe; }
.stat-purple { background: #f5f3ff; border-color: #ddd6fe; }
.stat-red    { background: #fef2f2; border-color: #fecaca; }
.stat-icon { width: 22px; height: 22px; margin: 0 auto 8px; display: block; }
.stat-green  .stat-icon { color: #16a34a; }
.stat-blue   .stat-icon { color: #2563eb; }
.stat-purple .stat-icon { color: #7c3aed; }
.stat-red    .stat-icon { color: #dc2626; }
.stat-number { font-size: 26px; font-weight: 800; line-height: 1; }
.stat-green  .stat-number { color: #16a34a; }
.stat-blue   .stat-number { color: #2563eb; }
.stat-purple .stat-number { color: #7c3aed; }
.stat-red    .stat-number { color: #dc2626; }
.stat-label { font-size: 11px; font-weight: 500; color: #64748b; margin-top: 4px; }

.absensi-progress-wrap { padding: 0 20px 18px; }
.absensi-progress-label {
  display: flex; justify-content: space-between;
  font-size: 12.5px; color: #475569; margin-bottom: 6px;
}
.absensi-progress-label strong { color: #0d2b5e; }
.absensi-progress-track {
  height: 8px; background: #e2e8f0; border-radius: 99px; overflow: hidden;
}
.absensi-progress-fill {
  height: 100%; background: linear-gradient(90deg, #0d2b5e, #1a4a9e);
  border-radius: 99px; transition: width .6s ease;
}

/* ── Patroli Summary ── */
.patroli-summary-grid { padding: 16px 20px; display: flex; flex-direction: column; gap: 16px; }
.patroli-area-name { font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 6px; }
.patroli-progress-row { display: flex; align-items: center; gap: 10px; }
.patroli-track {
  flex: 1; height: 7px; background: #e2e8f0; border-radius: 99px; overflow: hidden;
}
.patroli-fill { height: 100%; background: linear-gradient(90deg, #0d2b5e, #1a4a9e); border-radius: 99px; }
.patroli-fill-done { background: linear-gradient(90deg, #16a34a, #22c55e); }
.patroli-fill-warn { background: linear-gradient(90deg, #d97706, #f59e0b); }
.patroli-pct { font-size: 12px; font-weight: 700; color: #334155; min-width: 36px; text-align: right; }
.patroli-status { font-size: 11.5px; font-weight: 600; margin-top: 5px; }
.patroli-status-ok   { color: #0d2b5e; }
.patroli-status-done { color: #16a34a; }
.patroli-status-warn { color: #d97706; }

/* ── Approval List ── */
.approval-list { padding: 8px 0; }
.approval-item {
  display: flex; align-items: center; gap: 12px;
  padding: 14px 20px; text-decoration: none;
  transition: background .12s; cursor: pointer;
}
.approval-item:hover { background: #f8fafc; }
.approval-item + .approval-item { border-top: 1px solid #f1f5f9; }
.approval-icon-wrap {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.approval-icon-blue   { background: #eff6ff; }
.approval-icon-purple { background: #f5f3ff; }
.approval-icon-orange { background: #fffbeb; }
.approval-icon { width: 18px; height: 18px; }
.approval-icon-blue   .approval-icon { color: #2563eb; }
.approval-icon-purple .approval-icon { color: #7c3aed; }
.approval-icon-orange .approval-icon { color: #d97706; }
.approval-body { flex: 1; }
.approval-title { font-size: 13.5px; font-weight: 600; color: #1e293b; }
.approval-desc  { font-size: 12px; color: #64748b; margin-top: 2px; }
.approval-badge {
  font-size: 12px; font-weight: 700; min-width: 24px; height: 24px;
  border-radius: 99px; display: flex; align-items: center; justify-content: center;
  padding: 0 6px;
}
.approval-badge-blue   { background: #dbeafe; color: #1d4ed8; }
.approval-badge-purple { background: #ede9fe; color: #6d28d9; }
.approval-badge-orange { background: #fef3c7; color: #b45309; }
.approval-arrow { width: 16px; height: 16px; color: #94a3b8; flex-shrink: 0; }

/* ── Activity Feed ── */
.activity-feed { padding: 8px 20px 12px; }
.activity-item {
  display: flex; align-items: flex-start; gap: 12px;
  padding: 10px 0;
}
.activity-item + .activity-item { border-top: 1px solid #f1f5f9; }
.activity-dot {
  width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 4px;
}
.dot-green  { background: #16a34a; }
.dot-blue   { background: #2563eb; }
.dot-orange { background: #d97706; }
.dot-red    { background: #dc2626; }
.activity-content { flex: 1; }
.activity-name { font-size: 13px; font-weight: 600; color: #1e293b; }
.activity-desc { font-size: 12px; color: #64748b; margin-top: 1px; }
.activity-time { font-size: 11.5px; color: #94a3b8; font-weight: 500; flex-shrink: 0; }

/* ── Responsive ── */
@media (max-width: 1100px) {
  .db-main-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .db-kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .absensi-stat-grid { grid-template-columns: repeat(2, 1fr); }
  .db-topbar { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 480px) {
  .db-kpi-grid { grid-template-columns: 1fr 1fr; }
}
</style>